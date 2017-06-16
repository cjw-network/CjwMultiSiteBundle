# CjwMultiSiteBundle Configuration

## Overview

CjwMultiSiteBundle aims to follow the eZ Platform configuration as closely as possible and at the same time attemps to reduce redundany in the config files as much as possible.

Therefore it allows for several levels of configuration:
* global base configuration, valid for all sites, in the `app_cjwmultisite` application folder
* site specific configuration, in the site's bundle
* "override" configuration (similar to `settings/override` in eZ Publish Legacy)

These are discussed in the following sections.

## `app_cjwmultisite` Application Folder

In a multisite setup, the `app_cjwmultisite` Application Folder takes over the function of the standard eZ Platform `app` folder.

### Kernels and Console

* `CjwPublishKernel.php`
* `CjwPublishKernel.php`

These kernels inherit (in)directly from eZ Platform's `AppKernel`. So all the bundles that are present in the standard eZ installation are activated by default. We have added further bundles that are available for all sites in the usual manner, and you may add to this list:

```php
    public function registerBundles()
    {
        $bundles = parent::registerBundles();

        $bundles[] = new Lolautruche\EzCoreExtraBundle\EzCoreExtraBundle();
        $bundles[] = new Netgen\Bundle\AdminUIBundle\NetgenAdminUIBundle();

        return $bundles;
    }
```
* `console`

The console is a bit special, see [Resources/doc/console.md](./console.md)

### `config` folder

#### Multisite configuration

`app_cjwmultisite/cjwmultisite.yml` contains a list of all bundles managed by CjwMultiSiteBundle. Note that you need to following some [naming conventions](conventions.md) for CjwMultiSiteBundle to work out of the box.

```yaml
cjwmultisite:
    # relative to ezroot/src/ folder
    active_site_bundles:
        - Company/SiteDemoOneBundle
        - Company/SiteDemoTwoBundle
```
**Note: Adding a reference to a new bundle is the only step that is needed to activate a new site in the system!** 

#### Other config files

The other config files are more or less unchanged from the standard eZ Platform installation. They are kept as close to the originals in `/app/config` as possible. Only `ezplatform.yml` is reduced to the repository settings which we assume is the same for all sites.

They privide a global base configuration, valid for all sites. In the site bundle you need only extend these files, thus reducing reduncancy and complexity of the system.

They are processed first, starting from `config_{environment}.yml`, as in the standard eZ installation.

### `config_override` folder

The config files in this folder are parsed after all other config files. This allows for overriding settings from the bundle. This comes in handy e.g. in the local development, where you work with common database credentials.


## SiteBundle

In fact, a site bundle compromises a whole eZ Platform app with its own kernel. Therefore we have introduced an `app` folder within a site bundle with its own kernels and configs.

```
SiteDemoBundle/
    app/
        config/
        Resources/
        SiteDemoCache.php
        SiteDemoKernel.php
```

### Kernels

Working with CjwMultiSiteBundle feels familiar. Instead of extending `AppKernel`, you extend `CjwPublishKernel`. You can then register site specfic bundles as you like.

```php
class CompanyDemoSiteOneKernel extends CjwPublishKernel
{
    /**
     * This method can be used to load additional bundles.
     * It should call parent::registerBundles in order to load all Bundles required to run EzPublish.
     *
     * @return array|\Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function registerBundles()
    {
        $bundles = parent::registerBundles();

        $bundles[] = new Acme\DemoBundle\AcmeDemoBundle();

        return $bundles;
    }

}
```
### Multisite config

`config/cjwmultisite.yml` contains a list of all domain names handled by this bundle. This list is a prerequisite to the siteaccess matching, which is done by standard matchers. 

Note that you need to following some naming conventions for CjwMultiSiteBundle to work out of the box.

```yaml
cjwmultisite:
    site:
        domains:
            - www.demo.com
            - www.demo.org
            - admin.demo.com
```

### `SiteBundle/app/config`

Add all your site specific parameters and configuration here, just as you would in the standard `app` folder.

As these config files are parsed after the global ones, you may omit all redundant items here.

### `SiteBundle/Resources/config`

Keep other configs here, like image variations, template overrides... Include them from `app/config/ezplatform.yml` or load them in the extension file as usual:

```php
class CompanyDemoSiteExtension extends Extension
{

    public function load( array $configs, ContainerBuilder $container )
    {
        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
        $loader->load( 'services.yml');
    }
```

## MultisiteBundle

Note: CjwMultiSiteBundle keeps its own config in `Cjw/MultiSiteBundle/app/config`. Do not change these settings unless you know what you do.


# Site Access Matchers

CjwMultiSiteBundle provides three additional siteaccess matchers, providing Host, URI and language matching and making the configuration of development, staging and production sites easier. See [Resources/doc/matcher.md](./matcher.md) for details.


# Routing

[WIP] CjwMultiSiteBundle provides an URLAliasRouter that is capable of generating routes across different siteaccess defined in the same bundle.
