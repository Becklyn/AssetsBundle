<?php

namespace Tests\Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Dependency\DependencyMap;
use Becklyn\AssetsBundle\Dependency\DependencyMapFactory;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Becklyn\AssetsBundle\File\FileLoader;
use Becklyn\AssetsBundle\Twig\AssetsTwigExtension;
use Becklyn\AssetsBundle\Url\AssetUrl;
use PHPUnit\Framework\TestCase;


class AssetsTwigExtensionTest extends TestCase
{
    /**
     * Assert that the exposed twig functions don't change
     */
    public function testMethodNames ()
    {
        $htmlReferences = $this->getMockBuilder(AssetHtmlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetUrl = $this->getMockBuilder(AssetUrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dependencyMapFactory = $this->getMockBuilder(DependencyMapFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new AssetsTwigExtension($htmlReferences, $assetUrl, $fileLoader, $dependencyMapFactory);
        $functions = \array_map(
            function (\Twig_SimpleFunction $f)
            {
                return $f->getName();
            },
            $extension->getFunctions()
        );

        self::assertArraySubset([
            "asset",
            "asset_inline",
            "assets_link",
        ], $functions);
    }
}
