<?php

namespace Cjw\MultiSiteBundle\Matcher;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\URI as BaseMapURI;

/**
 * Map/HostURI Matcher.
 *
 * Uses Hostname and URI to select siteaccess
 * Checks beginning of host name only, thus allowing generic host suffixes.
 *
 * eg. www.example.com/de, www.example.com.ez53.fw.lokal/de
 *
 * Use in ezplatform.yml as follows:
 *
 *     match:
 *         \Cjw\MultiSiteBundle\Matcher\MapHostURI:
 *                 www.example.com/de/(default): example_user_de
 *                 www.example.com/en: example_user_en
 *                 www.example.net/de: example_net_user_de
 *                 www.example.net/en: example_net_user_en
 *
 *  /(default)  => if no uri element is set, this value is used as default siteaccess
 * and the uri element is set to the defined value
 * in our case  http://www.example.com will select example_user_de and set the uri element to 'de'
 * so all rendered eZ urls will get a prefix 'de' so the next call is www.example.com/de/...
 */
class MapHostURI extends BaseMapURI
{
    private $isDefaultSiteAccessMatch = false;

    public function getName()
    {
        return 'hosturi:map';
    }

    /**
     * Fixes up $uri to remove the siteaccess part, if needed.
     *
     * @param string $uri The original URI
     *
     * @return string
     */
    public function analyseURI($uri)
    {
        if ($this->isDefaultSiteAccessMatch) {
            return $uri;
        } else {
            return substr($uri, strlen("/$this->key"));
        }
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false siteaccess matched or false
     */
    public function match()
    {
        // www.test.de.cjw1411.fw.lokal
        $requestHost = $this->request->host;
        // de
        $requestUriPart = $this->key;

        // host        => array( uripart, siteaccess )
        // www.test.de => array( 'de', 'test_user_de' )
        $defaultMapUriArray = array();

        $defaultHostKey = '';

        foreach ($this->map as $hostUri => $siteAccess) {
            // $hostUri =  www.test.de/de
            // $siteAccess = test_user_de
            $hostUriArray = explode('/', $hostUri);

            // www.test.de
            $host = $hostUriArray[0];
            $uriPart = '';
            if (isset($hostUriArray[1])) {
                // de
               $uriPart = $hostUriArray[1];

               // storing defaultsettings for host and uri part for later use
               if (isset($hostUriArray[2])) {
                   // www.test.de => array( de, test_user_de )
                   $defaultMapUriArray[$host] = array($uriPart, $siteAccess);
               }
            }

            // www.test.de.cjw1411.fw.lokal begins with www.test.de
            // und  de == de
            if (strpos($requestHost, $host) === 0) {
                $defaultHostKey = $host;
                if ($requestUriPart == $uriPart) {
                    // insert matched url to allow for reverse matching
                    //$this->map[$this->key] = $siteAccess;
                    return $siteAccess;
                }
            }
        }
        // no siteaccess found based on uri, use default if defined
        if (isset($defaultMapUriArray[$defaultHostKey])) {
            $uriPart = $defaultMapUriArray[$defaultHostKey][0];
            $defaultSiteAccess = $defaultMapUriArray[$defaultHostKey][1];

            // store uriPart for later => to remove from uri internaly
            $this->key = $uriPart;
            $this->isDefaultSiteAccessMatch = true;

            return $defaultSiteAccess;
        }

        return false;
    }

    /**
     * Reverse Matching of Siteaccess.
     *
     * @param string $siteAccessName
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher|Map|null
     */
    public function reverseMatch($siteAccessName)
    {
        $reverseMap = $this->getReverseMap($siteAccessName);
        if (!isset($reverseMap[$siteAccessName])) {
            return null;
        }

        $mapItems = explode('/', $reverseMap[$siteAccessName]);
        $this->setMapKey($mapItems[1]); // uri prefix

        $request = $this->getRequest();
        // Clean up "old" siteaccess prefix and add the new prefix.
        $request->setPathinfo($this->analyseLink($request->pathinfo));
        // @todo: check if host can be safely set here
        // maybe host must be extended if it is a begins with domain
        $request->setHost($mapItems[0]); // host

        return $this;
    }

    /**
     * Generate Reverse Map (needed as parent function is marked private).
     *
     * @param $defaultSiteAccess
     *
     * @return array
     */
    protected function getReverseMap($defaultSiteAccess)
    {
        if (!empty($this->reverseMap)) {
            return $this->reverseMap;
        }

        $map = $this->map;
        foreach ($map as &$value) {
            // $value can be true in the case of the use of a Compound matcher
            if ($value === true) {
                $value = $defaultSiteAccess;
            }
        }

        return $this->reverseMap = array_flip($map);
    }
}
