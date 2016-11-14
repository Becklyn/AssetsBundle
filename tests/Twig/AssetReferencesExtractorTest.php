<?php

namespace Becklyn\AssetsBundle\tests\Twig;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\Twig\AssetReferencesExtractor;
use Becklyn\AssetsBundle\Twig\Extension\AssetsTwigExtension;


class AssetReferencesExtractorTest extends \PHPUnit_Framework_TestCase
{
    private $fixturesDir;
    private $twig;
    private $loader;


    public function setUp ()
    {
        $this->fixturesDir = dirname(__DIR__) . "/fixtures/templates";

        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem($this->fixturesDir), [
            "cache" => false,
        ]);

        $pathGenerator = self::getMockBuilder(PathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->twig->addExtension(new AssetsTwigExtension($pathGenerator));
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


    public function testInheritance ()
    {
        $extractor = new AssetReferencesExtractor($this->twig);
        $assets = $extractor->extractAssetsFromFile("{$this->fixturesDir}/inheritance/inheritance.html.twig");

        self::assertCount(1, $assets);

        $asset = $assets[0];
        self::assertSame("b.css", $asset->getReference());
        self::assertSame(AssetReference::TYPE_STYLESHEET, $asset->getType());
    }
}
