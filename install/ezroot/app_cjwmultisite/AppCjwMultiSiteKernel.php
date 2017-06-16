<?php
/**
 * File containing the CjwPublishKernel class.
 *
 * @copyright Copyright (C) 2007-2014 CJW Network - Coolscreen.de, JAC Systeme GmbH, Webmanufaktur. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @filesource
 */
require_once __DIR__ . '/../vendor/cjw-network/multisite-bundle/app/CjwMultiSiteKernel.php';

class AppCjwMultiSiteKernel extends CjwMultiSiteKernel
{
    public function registerBundles()
    {
        $bundles = parent::registerBundles();

        $bundles[] = new Lolautruche\EzCoreExtraBundle\EzCoreExtraBundle();
        $bundles[] = new Netgen\Bundle\AdminUIBundle\NetgenAdminUIBundle();

        return $bundles;
    }
}
