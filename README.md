# CjwMultiSiteBundle

CjwMultiSiteBundle allows multiple fully independant sites within a single eZ Platform installation

## Compatibility
* `2.0` branch is fully compatible with eZ Platform
* `1.0` branch (available only bundled in [https://github.com/cjw-network/cjwpublish1411]()) is compatible with eZ Publish 5.4/2014.11 and the Netgen variant aka 2014.12

## Goals of this project

* Host many (smaller) sites on the same installation
* Central site administration (site activation, cronjobs, ...)
* Easy deployment from version control system
* Highly reduced maintenance costs (security patches, upgrades)
* Highly efficient use of hardware resources

## Features

* Boots kernel and environment based on domain name mappings
* Handles local, staging and live domain names
* Allows for global activation of bundles
* Allows for global settings
* Provides additional siteaccess matchers
* Provides a UrlAliasRouter capable of generating routes to other siteaccesses defined in the same bundle.
* Provides a common console for all sites
* Caches domain name mappings
* Moves cache and log files away from the ezpublish folder
* Lives side by side with the standard eZ Platform installation
* Dead simple activation of a new site: touch only one config file

## Prerequisites

CjwMultiSiteBundle requires eZ Platform with LegacyBridge installed. Currently, https://github.com/emodric/ezplatform-legacy provides the easiest installation. Please test the installation before proceeding.

We strongly recommend (and assume) that https://github.com/netgen/NetgenAdminUIBundle to be installed. Please follow the installation instructions closely and test the installation before proceeding.

## Installation

CjwMultiSiteBundle can be installed via `composer`. See [Resources/doc/installation.md](Resources/doc/installation.md) for instructions. Note that some manual tweaks are necessary.

## Documentation

See contents of [Resources/doc/](Resources/doc) folder.

## Developers

    Felix Woldt (@fwoldt), Donat Fritschy (@dfritschy)
    mailto:info@cjw-network.com

## Copyright & License

Copyright CJW Network, for copyright and license details see provided LICENSE file.