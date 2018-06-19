<?php

namespace Tests\Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Dependency\DependencyMap;
use Becklyn\AssetsBundle\Dependency\DependencyMapFactory;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\File\Type\Css\CssImportRewriter;
use Becklyn\AssetsBundle\File\Type\CssFile;
use Becklyn\AssetsBundle\File\Type\GenericFile;
use Becklyn\AssetsBundle\File\Type\JavaScriptFile;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Becklyn\AssetsBundle\Url\AssetUrl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Tests\Compiler\AutowireRequiredMethodsPassTest;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Tests\Becklyn\AssetsBundle\CreateHashedAssetTrait;


class AssetHtmlGeneratorTest extends TestCase
{
    use CreateHashedAssetTrait;

    private $js = [];
    private $css = [];

    protected function setUp ()
    {
        $this->js = [
            $this->createdHashedAsset("out", "js/first.js", "j1"),
            $this->createdHashedAsset("out", "js/second.js", "j2"),
        ];

        $this->css = [
            $this->createdHashedAsset("out", "css/first.css", "c1"),
            $this->createdHashedAsset("out", "css/second.css", "c2"),
        ];
    }


    protected function buildGenerator (bool $isDebug)
    {
        $registry = $this->getMockBuilder(AssetsRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetUrl = $this->getMockBuilder(AssetUrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $importRewriter = $this->getMockBuilder(CssImportRewriter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $importRewriter
            ->method("rewriteStaticImports")
            ->willReturnArgument(1);

        $fileTypeRegistry = new FileTypeRegistry(new GenericFile(), [
            "js" => new JavaScriptFile(),
            "css" => new CssFile($importRewriter),
        ]);

        $dependencyMapFactory = $this->getMockBuilder(DependencyMapFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dependencyMapFactory
            ->method("getDependencyMap")
            ->willReturn(new DependencyMap());

        $assetUrl
            ->method("generateUrl")
            ->willReturnCallback(function (Asset $asset) { return $asset->getAssetPath(); });

        $generator = new AssetHtmlGenerator($registry, $assetUrl, $fileTypeRegistry, $isDebug, $dependencyMapFactory);
        return [$generator, $registry, $assetUrl, $fileTypeRegistry];
    }


    public function testDebugJS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $assetUrl
         */
        [$generator, $registry, $assetUrl] = $this->buildGenerator(true);

        // the registry must not be called in debug mode
        $registry
            ->expects(self::never())
            ->method("get");

        /** @var Asset[] $assets */
        $assets = [
            new Asset("bundles", "a/first.js"),
            new Asset("bundles", "b/second.js"),
        ];

        $assetUrl
            ->expects(self::exactly(2))
            ->method("generateUrl")
            ->withConsecutive(...$assets);

        $html = $generator->linkAssets([$assets[0]->getAssetPath(), $assets[1]->getAssetPath()]);
        self::assertEquals(
            '<script defer src="@bundles/a/first.js"></script><script defer src="@bundles/b/second.js"></script>',
            $html
        );
    }


    public function testDebugCSS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $assetUrl
         */
        [$generator, $registry, $assetUrl] = $this->buildGenerator(true);

        // the registry must not be called in debug mode
        $registry
            ->expects(self::never())
            ->method("get");

        /** @var Asset[] $assets */
        $assets = [
            new Asset("bundles", "a/first.css"),
            new Asset("bundles", "b/second.css"),
        ];

        $assetUrl
            ->expects(self::exactly(2))
            ->method("generateUrl")
            ->withConsecutive(...$assets);

        $html = $generator->linkAssets([$assets[0]->getAssetPath(), $assets[1]->getAssetPath()]);
        self::assertEquals(
            '<link rel="stylesheet" href="@bundles/a/first.css"><link rel="stylesheet" href="@bundles/b/second.css">',
            $html
        );
    }


    public function testProductionJS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         */
        [$generator, $registry] = $this->buildGenerator(false);

        $registry
            ->method("get")
            ->willReturnCallback(
                function (Asset $asset)
                {
                    return $this->createdHashedAsset($asset->getNamespace(), $asset->getFilePath(), "hash");
                }
            );

        $html = $generator->linkAssets(["@a/first.js", "@b/second.js"]);
        self::assertContains('integrity="', $html);
    }


    public function testProductionCSS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         */
        [$generator, $registry] = $this->buildGenerator(false);

        $registry
            ->method("get")
            ->willReturnCallback(
                function (Asset $asset)
                {
                    return $this->createdHashedAsset($asset->getNamespace(), $asset->getFilePath(), "hash");
                }
            );

        $html = $generator->linkAssets(["@a/first.css", "@b/second.css"]);
        self::assertContains('integrity="', $html);
    }
}
