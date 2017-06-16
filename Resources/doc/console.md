# CjwMultiSiteBundle Console

In order to support independent kernels for every site app, we also create an independant `console` for every site. For convenience they all live in the `app_cjwmultisite` directory. 

The consoles actually are just *symlinks* to the common `console` . This trick allows to instantiate it with the corresponding kernel.

For every SiteBundle, a symlink must be created. For your convenience, this script creates symlinks for all SiteBundles defined in `app_cjwmultisite/config/cjwmultisite.yml`:

```bash
$ php app_cjwmultisite/console --create-symlinks
```
You may also create the symlinks manually as follows (given bundle names of `SiteDemoBundle` and `SiteProjectNameBundle`)

```bash
$ cd app_cjwmultisite
$ ln -s console console-demo
$ ln -s console console-project-name
```
You then work with the consoles as you usually would:
```bash
$ php/app_cjwmultisite/console-demo cache:clear --env=dev
$ php/app_cjwmultisite/console-demo assets:install --symlink
```
