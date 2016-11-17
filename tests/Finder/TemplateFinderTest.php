<?php

namespace Becklyn\AssetsBundle\tests\Finder;

use Becklyn\AssetsBundle\Finder\TemplateFinder;
use Becklyn\AssetsBundle\tests\BaseTest;


class TemplateFinderTest extends BaseTest
{
    /**
     * @group template-finder
     */
    public function testTemplateDiscovery ()
    {
        $baseDir = $this->getFixturesDirectory("templates");

        $templateFinder = new TemplateFinder();
        $templates = $templateFinder->findInDirectory($baseDir);

        self::assertContains($baseDir . "/example.html.twig", $templates);
        self::assertContains($baseDir . "/nested/nestedExample.html.twig", $templates);
    }


    /**
     * @group template-finder
     */
    public function testInvalidSearchDirectory ()
    {
        $templateFinder = new TemplateFinder();
        $templates = $templateFinder->findInDirectory($this->getFixturesDirectory("/templates/fixtures/_invalid_folder_path_"));

        self::assertCount(0, $templates);
    }


    /**
     * @group template-finder
     */
    public function testEmptyResultForEmptySearchDirectory ()
    {
        $templateFinder = new TemplateFinder();
        $templates = $templateFinder->findInDirectory($this->getFixturesDirectory("/templates/fixtures/no-templates"));

        self::assertCount(0, $templates);
    }
}
