<?php
/**
 * File containing the CjwMultiSiteKernel class.
 *
 * @copyright Copyright (C) 2007-2014 CJW Network - Coolscreen.de, JAC Systeme GmbH, Webmanufaktur. All rights reserved.
 * @license   http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version   //autogentag//
 * @filesource
 */
use Symfony\Component\Config\Loader\LoaderInterface;

require_once __DIR__ . '/../../../../app/AppKernel.php';

class CjwMultiSiteKernel extends AppKernel
{
    // test-project
    protected $siteProjectName = null;
    // SiteTestProjectKernel
    protected $siteKernelClassName = null;

    // SiteTestProjectBundle
    protected $siteKernelBundleName = null;

    protected $siteEzPublishLegacyRootDir = null;
    protected $siteCacheDir = null;
    protected $siteLogDir = null;

    protected $siteSeparateVarCacheDir = false;
    protected $siteSeparateVarLogDir = false;

    /**
     * Constructor.
     *
     * @param string  $environment The environment
     * @param bool $debug       Whether to enable debugging or not
     *
     * @api
     */
    public function __construct($environment, $debug)
    {
        // temporarily fix libxml2-2.9.2 bug used in fedora 22 and other distros
        // https://jira.ez.no/browse/EZP-23846
        // https://www.drupal.org/node/2386903
        libxml_use_internal_errors(true);

        $this->siteProjectName = $this->getSiteName();
        $this->siteKernelClassName = $this->getSiteKernelClassName();
        $this->siteKernelBundleName = $this->getSiteKernelBundleName();

        // .../web
        $webDir = getcwd();
        /*
        // webmode
        if ( isset( $_SERVER['HTTP_HOST'] ) )
        {
            $this->siteEzPublishLegacyRootDir = "{$webDir}/../ezpublish_legacy";
        }
        else
        {
            // cli
            // php ./app_cjwmultisite/console ....
            $this->siteEzPublishLegacyRootDir = "{$webDir}/ezpublish_legacy";
        }
        */
        $webDir = preg_replace('/\/web$/', '', $webDir);
        $this->siteEzPublishLegacyRootDir = "{$webDir}/ezpublish_legacy";
        //$this->siteSeparateVarCacheDir = false;
        //$this->siteSeparateVarLogDir = false;

        parent::__construct($environment, $debug);
    }

    /**
     *  generate the Projectname from Kernel Class Name.
     *
     * e.g.  CjwSiteMyProjectKernel   => my-project
     *       CjwSiteCjwNetworkKernel  => cjw-network
     *       CjwSiteCjwnetworkKernel  => cjwnetwork
     *
     * @return string name of the site e.g. site_default
     */
    public function getSiteName()
    {
        $kernelClassName = $this->getSiteKernelClassName();

        // Vor Großbuchstaben ein leerzeichen setzen
        $siteProjectName = preg_replace('/([A-Z])/', ' $1', $kernelClassName);

        $kernelNameComponents = explode(' ', trim($siteProjectName));
        array_shift($kernelNameComponents);
        array_shift($kernelNameComponents);
        array_pop($kernelNameComponents);

        $siteProjectName = strtolower(implode('-', $kernelNameComponents));

        return $siteProjectName;
    }

    /**
     * @return string SiteTestProjectKernel
     */
    public function getSiteKernelClassName()
    {
        // return the ClassName Site...Kernel
        return get_called_class();
    }

    /**
     * This method can be used to load additional bundles.
     * It should call parent::registerBundles in order to load all Bundles required to run EzPublish.
     *
     * @return array|\Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function registerBundles()
    {
        $bundles = parent::registerBundles();

        $bundles[] = new Cjw\MultiSiteBundle\CjwMultiSiteBundle();

        return $bundles;
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @api
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $environment = $this->getEnvironment();

        // first, read global config files
        $configFileGlobal = $this->rootDir . '/../../../../app_cjwmultisite/config/config_' . $environment . '.yml';
        if (!is_readable($configFileGlobal)) {
            throw new RuntimeException("Configuration file '$configFileGlobal' is not readable.");
        }
        $loader->load($configFileGlobal);

        // read config files needed for CjwMultiSiteKernel
        $reflector = new ReflectionClass('CjwMultiSiteKernel');
        $classFileName = $reflector->getFileName();
        $dir = substr($classFileName, 0, strrpos($classFileName, '/'));
        $loader->load($dir . '/config/config_' . $environment . '.yml');

        // read bundle config files
        parent::registerContainerConfiguration($loader);

        // finally, allow for global overrides of configs
        $configFileGlobal = $this->rootDir . '/../../../../app_cjwmultisite/config_override/config_' . $environment . '.yml';
        if (is_readable($configFileGlobal)) {
            $loader->load($configFileGlobal);
        }
    }

    /**
     * Return Kernel Name.
     *
     * @return string
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name = preg_replace('/[^a-zA-Z0-9_]+/', '', $this->siteKernelClassName);
        }

        return $this->name;
    }

    /**
     * Define the cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        if (null === $this->siteCacheDir) {
            $varCacheDir = 'var';
            if ($this->siteSeparateVarCacheDir) {
                $varCacheDir = 'var_cache';
            }
            $this->siteCacheDir = "{$this->siteEzPublishLegacyRootDir}/{$varCacheDir}/{$this->siteProjectName}/cache_ezp/{$this->environment}";
        }

        return $this->siteCacheDir;
    }

    /**
     * Define the log directory.
     *
     * @return string
     */
    public function getLogDir()
    {
        if (null === $this->siteLogDir) {
            $varLogDir = 'var';
            if ($this->siteSeparateVarLogDir) {
                $varLogDir = 'var_log';
            }
            $this->siteLogDir = "{$this->siteEzPublishLegacyRootDir}/{$varLogDir}/{$this->siteProjectName}/log_ezp";
        }

        return $this->siteLogDir;
    }

    /**
     * Return the bundle name. e.g. SiteTestProjectBundle.
     *
     * @return string
     */
    public function getSiteKernelBundleName()
    {
        $kernelClassName = $this->getSiteKernelClassName();
        $bundleName = str_replace('Kernel', 'Bundle', $kernelClassName);

        return $bundleName;
    }

    /**
     * Add CJW Kernel settings to Kernel Parameters.
     *
     * @return array
     */
    protected function getKernelParameters()
    {
        $result = array_merge(
            parent::getKernelParameters(),
            array(
                'cjw_kernel.site_name' => $this->siteProjectName,
                'cjw_kernel.bundle_name' => $this->siteKernelBundleName,
            )
        );

        return $result;
    }
}
