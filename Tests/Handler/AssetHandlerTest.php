<?php

namespace Becklyn\AssetsBundle\tests\Handler;

use Becklyn\AssetsBundle\Assets\AssetsManager;
use Becklyn\AssetsBundle\Cache\AssetCache;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Finder\TemplateFinder;
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
            ->disableOriginalConstructor()
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

        $assetHandler = new AssetsManager($this->kernel, $this->assetCache, $this->templateFinder, $this->assetReferenceExtractor);

        $assetHandler->regenerateCache($this->symfonyStyle);
    }


    /**
     * @return array
     */
    public function dpRegenerateIndexesBundle ()
    {
        $assetReferenceA = new AssetReference("a.js", AssetReference::TYPE_JAVASCRIPT);
        $assetReferenceB = new AssetReference("b.css", AssetReference::TYPE_STYLESHEET);
        $assetReferenceC = new AssetReference("c.js", AssetReference::TYPE_JAVASCRIPT);
        $assetReferenceD = new AssetReference("d.css", AssetReference::TYPE_STYLESHEET);

        return [
            // Indexes:
            //   1: Template name
            //   2: Asset References
            //   3: Expected Output
            "No Tags" => ["no-tags.html.twig", [], "Template '../no-tags.html.twig': 0 references found"],
            "JavaScript Tag" => ["javascript-tag.html.twig", [$assetReferenceA], "Template '../javascript-tag.html.twig': 1 reference found"],
            "Stylesheet Tag" => ["stylesheet-tag.html.twig", [$assetReferenceB], "Template '../stylesheet-tag.html.twig': 1 reference found"],
            "Both Tags" => ["both-tags.html.twig", [$assetReferenceC, $assetReferenceD], "Template '../both-tags.html.twig': 2 references found"],
        ];
    }


    /**
     * @dataProvider dpRegenerateIndexesBundle
     * @group asset-handler
     * @group bundle
     * @group template-finder
     * @group asset-cache
     *
     * @param string           $templateName
     * @param AssetReference[] $assetReferences
     * @param string           $expectedTemplateResult
     */
    public function testRegenerateIndexesBundle (string $templateName, array $assetReferences, string $expectedTemplateResult)
    {
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

        $this->kernel
            ->expects($this->once())
            ->method("getBundles")
            ->willReturn([$bundle]);

        $this->templateFinder
            ->expects($this->once())
            ->method("findInDirectory")
            ->with("path")
            ->willReturn([$templateName]);

        $this->assetReferenceExtractor
            ->expects($this->once())
            ->method("extractAssetsFromFile")
            ->with($templateName)
            ->willReturn($assetReferences);

        $this->assetCache
            ->expects($this->at(0))
            ->method("clear");

        for ($i = 0; $i < count($assetReferences); $i++)
        {
            $this->assetCache
                // Increment the "at"-index to circumvent the following problem: http://stackoverflow.com/a/3425552
                ->expects($this->at($i + 1))
                ->method("add")
                ->with($assetReferences[$i]);
        }

        $this->symfonyStyle
            ->expects($this->once())
            ->method("comment")
            ->with("Removing existing asset files");

        $this->symfonyStyle
            ->expects($this->once())
            ->method("text")
            ->with($expectedTemplateResult);

        $assetHandler = new AssetsManager($this->kernel, $this->assetCache, $this->templateFinder, $this->assetReferenceExtractor);
        $assetHandler->regenerateCache($this->symfonyStyle);
    }
}
