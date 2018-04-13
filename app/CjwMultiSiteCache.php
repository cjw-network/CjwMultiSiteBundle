<?php
/**
 * File containing the CjwMultiSiteCache class.
 *
 * @copyright Copyright (C) 2007-2014 CJW Network - Coolscreen.de, JAC Systeme GmbH, Webmanufaktur. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @filesource
 */

use EzSystems\PlatformHttpCacheBundle\AppCache as PlatformHttpCacheBundleAppCache;

/**
 * Class CjwMultiSiteCache.
 *
 * For easier upgrade do not change this file, as of 2015.01 possible to extend
 * cleanly via SYMFONY_HTTP_CACHE_CLASS & SYMFONY_CLASSLOADER_FILE env variables!
 */
class CjwMultiSiteCache extends PlatformHttpCacheBundleAppCache
{
}
