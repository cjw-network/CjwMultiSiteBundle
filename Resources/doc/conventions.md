# CjwMultiSiteBundle Naming Conventions

You need to following some conventions for CjwMultiSiteBundle to work out of the box.

## Directory Structure

```
src/
    Company/
        SiteDemoOneBundle
        SiteDemoTwoBundle
```

## Bundle Names

In order to be recognized by CjwMultiSiteBundle, the bundle name **must** start with `Site` and end with `Bundle`. For the site name in between see below.

## Site Name

The site name defines not only the bundle's kernel names, but also the name of a eZ Publish legacy extension and the cache and log directories for this bundle.

The site name is a single capitalized word, or a CamelCase combination of several words.

| Site Name   | Extension         | Kernel                       | LogDir                             | CacheDir                           |
| ----------- | ----------------- | ---------------------------- | ---------------------------------- | ---------------------------------- |
| Demo        | site_demo         | CompanySiteDemoKernel        | web/var/site_demo/log_ezp         | web/var/site_demo/cache_ezp       |
| ProjectName | site_project-name | CompanySiteProjectNameKernel | web/var/site_project-name/log_ezp | web/var/site_project-name/log_ezp |

