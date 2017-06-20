<?php
/**
 * File containing the app_cjwmultisite/console logic.
 *
 * @copyright Copyright (C) 2007-2014 CJW Network - Coolscreen.de, JAC Systeme GmbH, Webmanufaktur. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @filesource
 */
use eZ\Bundle\EzPublishCoreBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;

require_once __DIR__ . '/CjwMultiSiteKernelMatcher.php';

$siteMatcher = new CjwMultiSiteKernelMatcher();
$siteName = $siteMatcher->getSiteNameFromCommandLine($argv[0]);

//echo "This is the sitename: \n".$siteName."\n";

$createSiteSymlinks = false;

if (isset($argv[1]) && $argv[1] == '--create-symlinks') {
    $createSiteSymlinks = true;
} elseif (false === $siteName) {
    echo "\n### No Sitename in console filename found - use the following syntax ###\n\n";
    echo "\tphp ./app_cjwmultisite/console-sitename\n";
    echo "\nNotice: For every active SiteBundle you have to create a symlink:\n\n";
    echo "\tcd ezroot/app_cjwmultisite\n";
    echo "\tln -s console console-sitename\n";

    echo "\nNotice: You can create all symlinks for all active SiteBundles with the following option:\n\n";
    echo "\tphp ./app_cjwmultisite/console --create-symlinks\n\n\n";

    return;
} else {
    //    echo "### Using SiteName: $siteName ###\n\n";
}

$ezrootDir = realpath(__DIR__ . '/../../../..');

set_time_limit(0);

// Use autoload over boostrap here so we don't need to keep the generated files in git
require_once $ezrootDir . '/app/autoload.php';

$input = new ArgvInput();
$env = $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'dev');

$debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(array('--no-debug', '')) && $env !== 'prod';
if ($debug) {
    Debug::enable();
}

require_once __DIR__ . '/CjwMultiSiteKernelMatcher.php';

// force cache regeneration when using console
$cjwMulitSiteKernelMatcher = new CjwMultiSiteKernelMatcher(true);
$cjwConfigArray = $cjwMulitSiteKernelMatcher->loadCjwPublishConfig(true);

//var_dump( $cjwConfigArray );

// create all console-sitename links for alle active Sites
if ($createSiteSymlinks === true) {
    echo "\n### Start Creating  Symlinks for cosole script for all active SiteBundles ###\n";

    foreach ($cjwConfigArray['sitename_array'] as $siteName2 => $siteNameArray) {
        // app_cjwmultisite/console-my-project
        $link = "app_cjwmultisite/console-$siteName2";

        if (!file_exists($link)) {
            echo "\n[symlink] create:  $link => app_cjwmultisite/console";
            symlink('console', $link);
        } else {
            echo "\n[symlink] exists:  $link => app_cjwmultisite/console";
        }
    }

    echo "\n\n### Symlinks creation Done ###\n";

    return;
}

$kernelInfoArray = $cjwMulitSiteKernelMatcher->getKernelInfosBySiteName($siteName);

//var_dump( $kernelInfoArray );

$siteAppPath = $kernelInfoArray['site_app_path'];
$siteKernelClassName = $kernelInfoArray['site_kernel_class_name'];

//echo "## lade kernel $siteKernelClassName ##\n";

require_once $siteAppPath . '/' . $siteKernelClassName . '.php';

$application = new Application(new $siteKernelClassName($env, $debug));
$application->run($input);
