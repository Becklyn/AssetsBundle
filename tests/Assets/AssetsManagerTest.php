<?php

namespace Becklyn\AssetsBundle\tests\Assets;

use Becklyn\AssetsBundle\Assets\AssetsManager;
use Becklyn\AssetsBundle\Cache\AssetsCache;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Finder\TemplateFinder;
use Becklyn\AssetsBundle\tests\BaseTest;
use Becklyn\AssetsBundle\Twig\AssetReferencesExtractor;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;


class AssetsManagerTest extends BaseTest
{

    public function testRegenerate ()
    {
        // prepare mocks
        $kernel = self::getMockBuilder(Kernel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetsCache = self::getMockBuilder(AssetsCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateFinder = self::getMockBuilder(TemplateFinder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $referencesExtractor = self::getMockBuilder(AssetReferencesExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exampleBundle = self::getMockBuilder(Bundle::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exampleReference = new AssetReference("", "js");

        // define expected calls
        $assetsCache
            ->expects(self::once())
            ->method("clear");

        $kernel
            ->expects(self::once())
            ->method("getBundles")
            ->willReturn([$exampleBundle]);

        $exampleBundle
            ->expects(self::once())
            ->method("getPath")
            ->willReturn("/bundle/path/");

        $templateFinder
            ->expects(self::once())
            ->method("findInDirectory")
            ->with(self::equalTo("/bundle/path/"))
            ->willReturn(["template.twig"]);

        $referencesExtractor
            ->expects(self::once())
            ->method("extractAssetsFromFile")
            ->with(self::equalTo("template.twig"))
            ->willReturn([$exampleReference]);

        $assetsCache
            ->expects(self::once())
            ->method("add")
            ->with($exampleReference);

        // run
        $assetsManager = new AssetsManager($kernel, $assetsCache, $templateFinder, $referencesExtractor);
        $assetsManager->regenerateCache();
    }
}
