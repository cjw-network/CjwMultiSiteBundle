<?php

namespace Cjw\MultiSiteBundle\Matcher;

/**
 * Map/HostURILanguage Matcher.
 *
 * Uses Hostname and URI to select siteaccess
 * but attempts to use the browser language for default host matching
 *
 * If no matching entry for browser language is found, uses default match if definesf
 *
 * eg. www.example.com/de, www.example.com.ez53.fw.lokal/de
 *
 * Use in ezplatform.yml as follows:
 *
 *     match:
 *         \Cjw\MultiSiteBundle\Matcher\MapHostURILanguage:
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
class MapHostURILanguage extends MapHostURI
{
    private $isDefaultSiteAccessMatch = false;

    public function getName()
    {
        return 'hosturilanguage:map';
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
        $uriMap = array();

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
                } else {
                    $uriMap[$uriPart] = $siteAccess;
                }
            }
        }
        // no siteaccess found based on uri, try language
        if (!$this->key) {
            foreach ($this->request->languages as $language) {
                $language = substr($language, 0, 2);
                if (array_key_exists($language, $uriMap)) {
                    $this->key = $language;

                    return $uriMap[$language];
                }
            }
        }

        // no siteaccess found based on uri and language, use default if defined
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
}
