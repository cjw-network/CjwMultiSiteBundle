<?php
/**
 * File containing the CjwMultiSite app_cjw.php logic.
 *
 * @copyright Copyright (C) 2007-2014 CJW Network - Coolscreen.de, JAC Systeme GmbH, Webmanufaktur. All rights reserved.
 * @license   http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version   //autogentag//
 * @filesource
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

$ezrootDir = realpath(__DIR__ . '/../../../..');

// Ensure UTF-8 is used in string operations
setlocale(LC_CTYPE, 'C.UTF-8');

// Environment is taken from "SYMFONY_ENV" variable, if not set, defaults to "prod"
$environment = getenv('SYMFONY_ENV');
if ($environment === false) {
    $environment = 'prod';
}

// Depending on the SYMFONY_DEBUG environment variable, tells whether Symfony should be loaded with debugging.
// If not set, or "", it is auto activated if in "dev" environment.
if (($useDebugging = getenv('SYMFONY_DEBUG')) === false || $useDebugging === '') {
    $useDebugging = $environment === 'dev';
}

// Depending on SYMFONY_CLASSLOADER_FILE use custom class loader, otherwise use bootstrap cache, or autoload in debug
if ($loaderFile = getenv('SYMFONY_CLASSLOADER_FILE')) {
    require_once $loaderFile;
} else {
    require_once $ezrootDir . '/app/autoload.php';
    if (!$useDebugging) {
        require_once $ezrootDir . '/app/bootstrap.php.cache';
    }
}

// Begin Site Matching

require_once $ezrootDir . '/vendor/cjw-network/multisite-bundle/app/CjwMultiSiteKernelMatcher.php';

$useSiteCache = true;
if ($environment == 'dev') {
    $useSiteCache = false;
}
$cjwMulitSiteKernelMatcher = new CjwMultiSiteKernelMatcher($useSiteCache);

// The site may be selected using the SITE environment variable.
$siteName = getenv('SITE');

// Use MultiSiteMatcher to select site based on Host Names
if ($siteName === false) {
    $kernelInfoArray = $cjwMulitSiteKernelMatcher->getKernelInfosByHostName();
} else {
    $kernelInfoArray = $cjwMulitSiteKernelMatcher->getKernelInfosBySiteName($siteName);
}

$siteAppPath = $kernelInfoArray['site_app_path'];
$kernelClassName = $kernelInfoArray['site_kernel_class_name'];
$cacheClassName = $kernelInfoArray['site_cache_class_name'];

if (!file_exists($siteAppPath)) {
    header('HTTP/1.0 404 Not Found');

    echo 'The site "' . $siteName . '" does not exist.';
    exit;
}

// End Site Matching

require_once $siteAppPath . DIRECTORY_SEPARATOR . $kernelClassName . '.php';

if ($useDebugging) {
    Debug::enable();
}

$kernel = new $kernelClassName($environment, $useDebugging);

// we don't want to use the classes cache if we are in a debug session
if (!$useDebugging) {
    $kernel->loadClassCache();
}

// Depending on the SYMFONY_HTTP_CACHE environment variable, tells whether the internal HTTP Cache mechanism is to be used.
// If not set, or "", it is auto activated if _not_ in "dev" environment.
if (($useHttpCache = getenv('SYMFONY_HTTP_CACHE')) === false || $useHttpCache === '') {
    $useHttpCache = $environment !== 'dev';
}

// Load HTTP Cache ...
if ($useHttpCache) {
    // The standard HttpCache implementation can be overridden by setting the SYMFONY_HTTP_CACHE_CLASS environment variable.
    // NOTE: Make sure to setup composer config so it is *autoloadable*, or use "SYMFONY_CLASSLOADER_FILE" for this.
    if ($httpCacheClass = getenv('SYMFONY_HTTP_CACHE_CLASS')) {
        $kernel = new $httpCacheClass($kernel);
    } else {
        require_once $siteAppPath . DIRECTORY_SEPARATOR . $cacheClassName . '.php';
        $kernel = new $cacheClassName($kernel);
    }
}

$request = Request::createFromGlobals();

// If you are behind one or more trusted reverse proxies, you might want to set them in SYMFONY_TRUSTED_PROXIES environment
// variable in order to get correct client IP
if ($trustedProxies = getenv('SYMFONY_TRUSTED_PROXIES')) {
    Request::setTrustedProxies(explode(',', $trustedProxies));
}

$response = $kernel->handle($request);

//
// Cache-Control	no-cache, must-revalidate
//
// client und proxies anhalten die Seite immer zu laden
// nur im http_cache lokal wird cache genriert mit ttls wie in ez gesetzt
// altes ez verhalten
// der server kontrolliert vollkommen das ausliefern der Seite
//$response->headers->set( 'Cache-Control', 'no-cache, must-revalidate' );

$response->send();
$kernel->terminate($request, $response);
