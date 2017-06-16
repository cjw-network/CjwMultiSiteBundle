# CjwMultiSiteBundle Installation

Currently there is no composer based installation. Please install CjwMultiSiteBundle manually as follows.

## Manual Installation

### Download and install files

Download the files and install them as `src/Cjw/MulitSiteBundle` folder within your eZ platform installation.

### Run the installation script

CjwMultiSiteBundle can live side by side with the standard eZ Platform installation. The installation script installs the following items, needed for the functioning of CjwMultiSiteBundle:
* a complement to `web/app.php`, called `app_cjw.php`
* its own application folder in the eZ Platform root directory, called `app_cjwmultisite`

To invoke the installation script, place yourself in the eZ Platform root directory and issue the following command:
```bash
$ sh vendor/cjw-network/multisite-bundle/install/install_cjwmultisite.sh
```
Note: The install script will not overwrite previousely installed files.

For a discussion about these items, see [Resources/doc/configuration.md](configuration.md)

### Web server configuration

#### Apache

We suggest that you create a separate VHOST configuration for every site.

Take the `/doc/apache2/vhost.template` from the eZ Platform installation as a basis, and locate the following line:
```apacheconfig
        RewriteRule .* /app.php
```
and change it to
```apacheconfig
        RewriteRule .* /app_cjw.php
```
Define `ServerName` and `ServerAlias` as needed.


#### Nginx

We suggest that you create a separate VHOST configuration for every site.

Take the `doc/nginx/vhost.template` from the eZ Platform installation as a basis. Define `server_name` as needed.

Then locate the following line in `doc/nginx/ez_params.d/ez_rewrite_params`:
```
rewrite "^(.*)$" "/app.php$1" last;
```
and change it to
```
rewrite "^(.*)$" "/app_cjw.php$1" last;
```

## Create and activate SiteBundles

In a CjwMultiSite environment, **every site is a separate bundle**. To make it work out of the box, please follow the naming conventions in [Resources/doc/conventions.md](./conventions.md).

First, create a new SiteBundle

    src/Company/SiteProjectNameBundle

Then activate the SiteBundle in `app_cjwmultisite/config/cjwmultisite.yml`

```yaml
cjwmultisite:
    active_site_bundles:
        - Company/SiteProjectNameBundle
        - ...
```

Set up the consoles for all active Sitebundles
```bash
$ php ./app_cjwmultisite/console --create-symlinks
```
Manage your project using the generated dedicated `console-project-name` command, e.g.
```bash
$ php ./app_cjwmultisite/console-project-name assets:install web --symlink
```
Learn more about the console in [Resources/doc/console.md](./console.md)
