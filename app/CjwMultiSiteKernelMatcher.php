<?php
/**
 * File containing the CjwMultiSiteKernelMatcher class.
 *
 * @copyright Copyright (C) 2007-2014 CJW Network - Coolscreen.de, JAC Systeme GmbH, Webmanufaktur. All rights reserved.
 * @license   http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version   //autogentag//
 * @filesource
 */
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class CjwMultiSiteKernelMatcher.
 *
 * Handles the logic to match a  SiteKernel
 *
 * TODO a global Logging for errors
 */
class CjwMultiSiteKernelMatcher
{
    const STASH_NS = 'cjwmultisite';
    const STASH_KEY = 'config_array';
    const STASH_TTL = 60;

    protected $activeSiteBundles = null;
    protected $configArray = null;
    protected $ezRootDir = null;

    private $useCache;

    public function __construct($useCache = true)
    {
        $this->ezRootDir = __DIR__ . '/../../../..';
        $this->useCache = $useCache;
    }

    /**
     * Reloads the global cache which inlcudes data from cjwmultisite.yml
     * can be called if a new site package is enabled on production.
     *
     * @return array
     */
    public function reloadCache()
    {
        return $this->loadCjwPublishConfig(true);
    }

    /**
     *  Load cjwmultisite.yml configuration, caching if needed.
     *
     * @param bool $forceGenerateCache if true than the cache if is active will be new generated
     *
     * @return array
     */
    public function loadCjwPublishConfig($forceGenerateCache = false)
    {
        if ($this->configArray === null) {
            if ($this->useCache === true) {
                $driver = new Stash\Driver\FileSystem();
                $pool = new Stash\Pool($driver);
                $pool->setNamespace($this::STASH_NS);
                $item = $pool->getItem($this::STASH_KEY);
                $data = $item->get();

                if ($item->isMiss() || $forceGenerateCache) {
                    $item->lock();
                    $data = $this->parseConfig();
                    $item->set($data);
                    $item->expiresAfter($this::STASH_TTL);
                    $pool->save($item);
                }
                $this->configArray = $data;
            } else {
                $this->configArray = $this->parseConfig();
            }
        }

        return $this->configArray;
    }

    /**
     * Parse cjwmultisite.yml configuration.
     *
     * @return array
     */
    private function parseConfig()
    {
        $activeSiteBundleArray = $this->getActiveSiteBundles();

        //var_dump( $activeSiteBundleArray );
        $domainKernelMatchArray = array();
        $siteNameArray = array();

        // $siteBundle = Jac/SiteSvvStralundBundle
        foreach ($activeSiteBundleArray as $siteBundleName) {
            // Vor Großbuchstaben ein leerzeichen setzen
            // Jac/SiteSvvStralsundBundle
            $siteBundleClassName = str_replace('/', '', $siteBundleName);

            // Jac Site Svv Stralsund Bundle
            $siteBundleClassNameWhitspace = preg_replace('/([A-Z])/', ' $1', $siteBundleClassName);
            $siteBundleClassNameComponents = explode(' ', trim($siteBundleClassNameWhitspace));

            //var_dump( $siteBundleClassNameComponents );

            // remove  ...Bundle from string
            array_pop($siteBundleClassNameComponents);

            // JacSiteSvvStralsund
            $siteClassBaseName = implode('', $siteBundleClassNameComponents);

            // remove  Jac from string
            array_shift($siteBundleClassNameComponents);
            // remove Site from string
            array_shift($siteBundleClassNameComponents);

            // svv-stralsund
            $siteName = strtolower(implode('-', $siteBundleClassNameComponents));

            $siteBundlePath = $this->ezRootDir . '/src/' . $siteBundleName;

            $siteYamlFile = $siteBundlePath . '/app/config/cjwmultisite.yml';

            if (file_exists($siteYamlFile)) {
                $siteArray = Yaml::parse(file_get_contents($siteYamlFile));

                if (isset($siteArray['cjwmultisite']['site']['domains'])) {
                    foreach ($siteArray['cjwmultisite']['site']['domains'] as $domain) {
                        $domainKernelMatchArray[$domain] = $siteName;
                    }
                } else {
                    // errror app config file
                }

                $siteNameArray[$siteName] = array(
                    'class_base_name' => $siteClassBaseName,
                    'app_path' => $siteBundlePath . '/app',
                );
            } else {
                // printf( "<br>Unable to find site cjwmultisite.yml: %s", $siteYamlFile );
            }
        }

        return array(
            'active_site_bundles' => $activeSiteBundleArray,
            'domain_array' => $domainKernelMatchArray,
            'sitename_array' => $siteNameArray,
        );
    }

    /**
     * @see  configfile /ezroot/app_cjwmultisite/config/cjwmultisite.yml
     * @return array with all Sitebundles which are activated
     */
    private function getActiveSiteBundles()
    {
        $sitesYamlFilePath = $this->ezRootDir . '/app_cjwmultisite/config/cjwmultisite.yml';

        try {
            $sitesArray = Yaml::parse(file_get_contents($sitesYamlFilePath));
            $activeSiteBundles = $sitesArray['cjwmultisite']['active_site_bundles'];
        } catch (ParseException $e) {
            printf('Unable to parse the YAML string: %s', $e->getMessage());

            return array();
        }

        return $activeSiteBundles;
    }

    /**
     * try to find the kernel to current Hostname.
     *
     * @param $hostName
     *
     * @return array
     */
    public function getKernelInfosByHostName($hostName = false)
    {
        $cjwPublishConfig = $this->loadCjwPublishConfig();

        $domainKernelMatchArray = $cjwPublishConfig['domain_array'];
        //$siteNameArray = $cjwPublishConfig['sitename_array'];

        if ($hostName === false) {
            // www.svv-stralsund.de.jac1311.fw.lokal
            $hostName = $_SERVER['HTTP_HOST'];
        }

        $kernelInfoArray = false;

        //
        // ################  Url auf Kernel Match
        //
        foreach ($domainKernelMatchArray as $matchMapHost => $siteName) {
            // $hostName begins with $domain

            // JAC beginns with
            if (strpos($hostName, $matchMapHost) === 0) {
                $kernelInfoArray = $this->getKernelInfosBySiteName($siteName);
                break;
            }
        }

        return $kernelInfoArray;
    }

    /**
     * sucht zu einem sitename  'svv-stralsund' die alle KernelParameter die benötigt werden
     * um den Site Kernel zu initialisieren.
     *
     * @param $siteName
     *
     * @return array
     */
    public function getKernelInfosBySiteName($siteName)
    {
        $cjwPublishConfig = $this->loadCjwPublishConfig();

        //        $domainKernelMatchArray = $cjwPublishConfig['domain_array'];
        $siteNameArray = $cjwPublishConfig['sitename_array'];

        //        echo '<br>' . $host . ' - ' . $matchMapHost . ' - '. $siteProjectName;
        $siteArray = $siteNameArray[$siteName];

        //  .. /app
        $siteAppPath = $siteArray['app_path'];
        $siteClassBaseName = $siteArray['class_base_name'];

        // SiteSvvStralsundKernel
        $kernelClassName = $siteClassBaseName . 'Kernel';
        // SiteSvvStralsundCache
        $cacheClassName = $siteClassBaseName . 'Cache';

        $kernelInfoArray = array(
            'site_name' => $siteName,
            'site_kernel_class_name' => $kernelClassName,
            'site_cache_class_name' => $cacheClassName,
            'site_app_path' => $siteAppPath,
        );

        return $kernelInfoArray;
    }
}
