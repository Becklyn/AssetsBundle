<?php

namespace Becklyn\AssetsBundle\tests\Handler;

use Becklyn\AssetsBundle\Cache\AssetCache;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Finder\TemplateFinder;
use Becklyn\AssetsBundle\Handler\AssetHandler;
use Becklyn\AssetsBundle\tests\BaseTest;
use Becklyn\AssetsBundle\Twig\AssetReferencesExtractor;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;


class AssetHandlerTest extends BaseTest
{
    /**
     * @var MockObject|KernelInterface
     */
    private $kernel;


    /**
     * @var MockObject|AssetCache
     */
    private $assetCache;


    /**
     * @var MockObject|TemplateFinder
     */
    private $templateFinder;


    /**
     * @var MockObject|AssetReferencesExtractor
     */
    private $assetReferenceExtractor;


    /**
     * @var MockObject|SymfonyStyle
     */
    private $symfonyStyle;


    public function setUp ()
    {
        $this->kernel = self::getMockBuilder(KernelInterface::class)
            ->getMock();

        $this->assetCache = self::getMockBuilder(AssetCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateFinder = self::getMockBuilder(TemplateFinder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetReferenceExtractor = self::getMockBuilder(AssetReferencesExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->symfonyStyle = self::getMockBuilder(SymfonyStyle::class)
            ->disableOriginalConstructor()
            ->getMock();
    }


    /**
     * @group asset-handler
     */
    public function testRegenerateCacheAlwaysPrintsRemovingExistingAssets ()
    {
        $this->kernel
            ->expects($this->once())
            ->method("getBundles")
            ->willReturn([]);

        $this->symfonyStyle
            ->expects($this->once())
            ->method("comment")
            ->with("Removing existing asset files");

        $assetHandler = new AssetHandler($this->kernel, $this->assetCache, $this->templateFinder, $this->assetReferenceExtractor);

        $assetHandler->regenerateCache($this->symfonyStyle);
    }


    /**
     * @group asset-handler
     */
    public function testRegenerateIndexesBundle ()
    {
        //region Kernel + Bundles
        $bundle = self::getMockBuilder(BundleInterface::class)
            ->getMock();

        $bundle
            ->expects($this->once())
            ->method("getPath")
            ->willReturn("path");

        $bundle
            ->expects($this->once())
            ->method("getName")
            ->willReturn("name");

        $bundles = [$bundle];

        $this->kernel
            ->expects($this->once())
            ->method("getBundles")
            ->willReturn($bundles);
        //endregion

        //region TemplateFinder
        $this->templateFinder
            ->expects($this->once())
            ->method("findInDirectory")
            ->with("path")
            ->willReturn([
                "no-tags.html.twig",
                "javascript-tag.html.twig",
                "stylesheet-tag.html.twig",
                "both-tags.html.twig",
            ]);
        //endregion

        //region AssetReferenceExtractor
        $assetReferenceA = new AssetReference("a.js", AssetReference::TYPE_JAVASCRIPT);
        $assetReferenceB = new AssetReference("b.css", AssetReference::TYPE_STYLESHEET);
        $assetReferenceC = new AssetReference("c.js", AssetReference::TYPE_JAVASCRIPT);
        $assetReferenceD = new AssetReference("d.css", AssetReference::TYPE_STYLESHEET);

        $this->assetReferenceExtractor
            ->expects($this->exactly(4))
            ->method("extractAssetsFromFile")
            ->withConsecutive(
                ["no-tags.html.twig"],
                ["javascript-tag.html.twig"],
                ["stylesheet-tag.html.twig"],
                ["both-tags.html.twig"]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                [$assetReferenceA],
                [$assetReferenceB],
                [$assetReferenceC, $assetReferenceD]
            );
        //endregion

        //region AssetCache
        $this->assetCache
            ->expects($this->once())
            ->method("clear");

        $this->assetCache
            ->expects($this->exactly(4))
            ->method("add")
            ->withConsecutive(
                [$assetReferenceA],
                [$assetReferenceB],
                [$assetReferenceC],
                [$assetReferenceD]
            );
        //endregion

        //region SymfonyStyle
        $this->symfonyStyle
            ->expects($this->once())
            ->method("comment")
            ->with("Removing existing asset files");

        $this->symfonyStyle
            ->expects($this->exactly(4))
            ->method("text")
            ->withConsecutive(
                ["Template '../no-tags.html.twig': 0 references found"],
                ["Template '../javascript-tag.html.twig': 1 reference found"],
                ["Template '../stylesheet-tag.html.twig': 1 reference found"],
                ["Template '../both-tags.html.twig': 2 references found"]
            );
        //endregion

        $assetHandler = new AssetHandler($this->kernel, $this->assetCache, $this->templateFinder, $this->assetReferenceExtractor);
        $assetHandler->regenerateCache($this->symfonyStyle);
    }
}
