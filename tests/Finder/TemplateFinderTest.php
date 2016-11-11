<?php

namespace Becklyn\AssetsBundle\tests\Finder;

use Becklyn\AssetsBundle\Finder\TemplateFinder;


class TemplateFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testTemplateDiscovery ()
    {
        $baseDir = dirname(__DIR__) . "/fixtures/templates";

        $templateFinder = new TemplateFinder();
        $templates = $templateFinder->findInDirectory($baseDir);

        self::assertContains($baseDir . "/example.html.twig", $templates);
        self::assertContains($baseDir . "/nested/nestedExample.html.twig", $templates);
    }
}
