<?php
/**
 * This file is part of the eZ Platform XmlText Field Type package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace Cjw\MultiSiteBundle\FieldType\XmlText\Converter;

use DOMDocument;
use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedToHtml5 as BaseEmbedToHtml5;

class EmbedToHtml5 extends BaseEmbedToHtml5
{
    protected function processTag(DOMDocument $xmlDoc, $tagName)
    {
        // <embed view="embed" size="medium" href="ezobject://99" />
        // q&d fix for embed problems in site_khf
        // if ($xmlDoc->textContent != '') {
        //    parent::processTag($xmlDoc, $tagName);
        // }
        parent::processTag($xmlDoc, $tagName);
    }
}
