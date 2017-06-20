<?php


use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/CjwMultiSiteKernelMatcher.php';

class CjwMultiSiteKernelMatcherTest extends TestCase
{
    /**
     * @dataProvider commandLineProvider
     */
    public function testGetSiteFromConsoleName($commandLine, $expected)
    {
        $matcher = new CjwMultiSiteKernelMatcher();
        $this->assertEquals($expected, $matcher->getSiteNameFromCommandLine($commandLine));
    }

    public function commandLineProvider()
    {
        return [
            ['app_cjwmultisite/console', ''],
            ['app_cjwmultisite/console-demo', 'demo'],
            ['app_cjwmultisite/console-acme', 'acme'],
            ['app_cjwmultisite/console-project-name', 'project-name'],
            ['/Users/me/daten/htdocs/ezplatform-legacy/app_cjwmultisite/console-project-name-one', 'project-name-one'],
            ['/Users/me/daten/htdocs/console/ezplatform-legacy/app_cjwmultisite/console-project-name-two', 'project-name-two'],
        ];
    }
}
