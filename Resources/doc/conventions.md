# CjwMultiSiteBundle Naming Conventions

You need to following some conventions for CjwMultiSiteBundle to work out of the box.

## Directory Structure

```
src/
    Company/
        SiteDemoBundle
        SiteProjectNameBundle
```

## Bundle Names

In order to be recognized by CjwMultiSiteBundle, the bundle name **must** start with `Site` and end with `Bundle`. For the site name in between see below.

## Site Name

The site name defines not only the bundle's kernel names, but also the name of a eZ Publish legacy extension and the cache and log directories for this bundle.

The site name is a single capitalized word, or a CamelCase combination of several words.

| Bundle Name           | Kernel                       | Console Sitename | Legacy Extension  | LogDir                       | CacheDir                     |
| --------------------- | ---------------------------- | ---------------- | ----------------- | ---------------------------- | ---------------------------- |
| SiteDemoBundle        | CompanySiteDemoKernel        | demo             | site_demo         | web/var/demo/log_ezp         | web/var/demo/cache_ezp       |
| SiteProjectNameBundle | CompanySiteProjectNameKernel | project-name     | site_project-name | web/var/project-name/log_ezp | web/var/project-name/log_ezp |

