<?php

namespace Becklyn\AssetsBundle\tests\Twig;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Twig\AssetReferencesExtractor;
use Becklyn\AssetsBundle\Twig\Extension\AssetsTwigExtension;


class AssetReferencesExtractorTest extends \PHPUnit_Framework_TestCase
{
    private $fixturesDir;
    private $twig;

    public function setUp ()
    {
        $this->fixturesDir = dirname(__DIR__) . "/fixtures/templates";

        $this->twig = new \Twig_Environment(new \Twig_Loader_Array([]), [
            "cache" => false,
        ]);

        $this->twig->addExtension(new AssetsTwigExtension());
    }


    public function testExtraction ()
    {
        $extractor = new AssetReferencesExtractor($this->twig);
        $assets = $extractor->extractAssetsFromFile($this->fixturesDir . "/example.html.twig");

        self::assertCount(1, $assets);

        $asset = $assets[0];
        self::assertSame("a.css", $asset->getReference());
        self::assertSame(AssetReference::TYPE_STYLESHEET, $asset->getType());
    }
}