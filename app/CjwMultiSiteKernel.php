<?php
/**
 * File containing the CjwMultiSiteKernel class.
 *
 * @copyright Copyright (C) 2007-2014 CJW Network - Coolscreen.de, JAC Systeme GmbH, Webmanufaktur. All rights reserved.
 * @license   http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version   //autogentag//
 * @filesource
 */
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class CjwMultiSiteKernel extends Kernel
{
    // test-project
    protected $siteProjectName = null;
    // SiteTestProjectKernel
    protected $siteKernelClassName = null;

    // SiteTestProjectBundle
    protected $siteKernelBundleName = null;

    protected $siteEzPublishLegacyRootDir = null;
    //protected $siteCacheDir = null;
    //protected $siteLogDir = null;

    //protected $siteSeparateVarCacheDir = false;
    //protected $siteSeparateVarLogDir = false;

    /**
     * Constructor.
     *
     * @param string $environment The environment
     * @param bool $debug Whether to enable debugging or not
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

        $this->siteEzPublishLegacyRootDir = "{$this->getProjectDir()}/ezpublish_legacy";
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
        $bundles = [];

        //$bundles[] = new Cjw\MultiSiteBundle\CjwMultiSiteBundle();

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
        // Load CjwMultiSiteBundle specific configuration
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
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
     * Gets the cache directory.
     *
     * @return string The cache directory
     */
    public function getCacheDir()
    {
        if (!empty($_SERVER['SYMFONY_TMP_DIR'])) {
            return rtrim(
                    $_SERVER['SYMFONY_TMP_DIR'],
                    '/'
                ).'/var/cache/'.$this->siteProjectName.'/'.$this->getEnvironment();
        }

        return $this->getProjectDir() .'/var/cache/'.$this->siteProjectName.'/'.$this->getEnvironment();
    }


    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir()
    {
        if (!empty($_SERVER['SYMFONY_TMP_DIR'])) {
            return rtrim($_SERVER['SYMFONY_TMP_DIR'], '/').'/var/logs/'.$this->siteProjectName;
        }

        return $this->getProjectDir() .'/var/logs/'.$this->siteProjectName;
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
