<?php
/**
 * File containing the CjwPublishKernel class.
 *
 * @copyright Copyright (C) 2007-2014 CJW Network - Coolscreen.de, JAC Systeme GmbH, Webmanufaktur. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @filesource
 */
use Symfony\Component\Config\Loader\LoaderInterface;

require_once __DIR__ . '/../vendor/cjw-network/multisite-bundle/app/CjwMultiSiteKernel.php';

class AppCjwMultiSiteKernel extends CjwMultiSiteKernel
{
    public function registerBundles()
    {
        $bundles = [
            // Symfony
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            // Dependencies
            new Hautelook\TemplatedUriBundle\HautelookTemplatedUriBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new FOS\HttpCacheBundle\FOSHttpCacheBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            // eZ Systems
            new EzSystems\PlatformHttpCacheBundle\EzSystemsPlatformHttpCacheBundle(),
            new eZ\Bundle\EzPublishCoreBundle\EzPublishCoreBundle(),
            new eZ\Bundle\EzPublishLegacySearchEngineBundle\EzPublishLegacySearchEngineBundle(),
            new eZ\Bundle\EzPublishIOBundle\EzPublishIOBundle(),
            new eZ\Bundle\EzPublishRestBundle\EzPublishRestBundle(),
            new EzSystems\EzSupportToolsBundle\EzSystemsEzSupportToolsBundle(),
            new EzSystems\PlatformInstallerBundle\EzSystemsPlatformInstallerBundle(),
            new EzSystems\RepositoryFormsBundle\EzSystemsRepositoryFormsBundle(),
            new EzSystems\EzPlatformSolrSearchEngineBundle\EzSystemsEzPlatformSolrSearchEngineBundle(),
            new EzSystems\EzPlatformDesignEngineBundle\EzPlatformDesignEngineBundle(),
            new EzSystems\EzPlatformAdminUiBundle\EzPlatformAdminUiBundle(),
            new EzSystems\EzPlatformAdminUiModulesBundle\EzPlatformAdminUiModulesBundle(),
            new EzSystems\EzPlatformAdminUiAssetsBundle\EzPlatformAdminUiAssetsBundle(),
            // CJW Multisite
            new Cjw\MultiSiteBundle\CjwMultiSiteBundle(),
            // Application
            new eZ\Bundle\EzPublishLegacyBundle\EzPublishLegacyBundle($this),
            new Lolautruche\EzCoreExtraBundle\EzCoreExtraBundle(),
            new Netgen\Bundle\AdminUIBundle\NetgenAdminUIBundle(),
            new Netgen\Bundle\RichTextDataTypeBundle\NetgenRichTextDataTypeBundle(),
            new EzSystems\EzPlatformXmlTextFieldTypeBundle\EzSystemsEzPlatformXmlTextFieldTypeBundle(),
        ];

        switch ($this->getEnvironment()) {
            case 'test':
            case 'behat':
                //$bundles[] = new EzSystems\BehatBundle\EzSystemsBehatBundle();
                //$bundles[] = new EzSystems\PlatformBehatBundle\EzPlatformBehatBundle();
                // No break, test also needs dev bundles
            case 'dev':
                $bundles[] = new Cjw\PlatformTestBundle\CjwPlatformTestBundle();
                $bundles[] = new eZ\Bundle\EzPublishDebugBundle\EzPublishDebugBundle();
                $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
                $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
                $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
                $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
                $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $env = $this->getEnvironment();

        // Load the base configuration common to all site
        $loader->load(__DIR__ . '/config/config_' . $env . '.yml');

        // Load CjwMultiSiteBundle specific configuration
        parent::registerContainerConfiguration($loader);

        // Finally, allow for global overrides of configs
        $configFileGlobal = __DIR__ . '/config_override/config_' . $env . '.yml';
        if (is_readable($configFileGlobal)) {
            $loader->load($configFileGlobal);
        }
    }
}
