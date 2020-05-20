<?php declare(strict_types=1);

namespace Tests\Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
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
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tests\Becklyn\AssetsBundle\CreateHashedAssetTrait;

class AssetHtmlGeneratorTest extends TestCase
{
    use CreateHashedAssetTrait;

    private $js = [];
    private $css = [];

    protected function setUp () : void
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


    public function testDebugJS () : void
    {
        /**
         * @var AssetHtmlGenerator                       $generator
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
         * @var \PHPUnit_Framework_MockObject_MockObject $assetUrl
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
            ->withConsecutive([$assets[0]], [$assets[1]]);

        $html = $generator->linkAssets([$assets[0]->getAssetPath(), $assets[1]->getAssetPath()]);
        self::assertSame(
            '<script defer src="@bundles/a/first.js"></script><script defer src="@bundles/b/second.js"></script>',
            $html
        );
    }


    public function testDebugCSS () : void
    {
        /**
         * @var AssetHtmlGenerator
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
         * @var \PHPUnit_Framework_MockObject_MockObject $assetUrl
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
            ->withConsecutive([$assets[0]], [$assets[1]]);

        $html = $generator->linkAssets([$assets[0]->getAssetPath(), $assets[1]->getAssetPath()]);
        self::assertSame(
            '<link rel="stylesheet" href="@bundles/a/first.css"><link rel="stylesheet" href="@bundles/b/second.css">',
            $html
        );
    }


    public function testProductionJS () : void
    {
        /**
         * @var AssetHtmlGenerator                       $generator
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
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
        self::assertContains(\sprintf('integrity="%s-hash', Asset::HASH_ALGORITHM), $html);
    }


    public function testProductionCSS () : void
    {
        /**
         * @var AssetHtmlGenerator                       $generator
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
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
        self::assertContains(\sprintf('integrity="%s-hash', Asset::HASH_ALGORITHM), $html);
    }


    /**
     * @return array
     */
    public function provideHttpImports () : array
    {
        return [
            "CSS file" => [
                [
                    "http://example.org/test.css",
                ],
                '<link rel="stylesheet" href="http://example.org/test.css">',
            ],
            "JS file" => [
                [
                    "http://example.org/test.js",
                ],
                '<script defer src="http://example.org/test.js"></script>',
            ],
            "JS file with query params" => [
                [
                    "http://example.org/test.js?query=abc",
                ],
                '<script defer src="http://example.org/test.js?query=abc"></script>',
            ],
            "JS file with unknown fragment, which will be ignored" => [
                [
                    "http://example.org/test.js#random=fragment",
                ],
                '<script defer src="http://example.org/test.js"></script>',
            ],
            "CSS file with unknown fragment, which will be ignored" => [
                [
                    "http://example.org/test.css#random=fragment",
                ],
                '<link rel="stylesheet" href="http://example.org/test.css">',
            ],
            "CSS file with explicit defined type" => [
                [
                    "http://example.org/test#type=css",
                ],
                '<link rel="stylesheet" href="http://example.org/test">',
            ],
            "CSS file with explicit defined type and an empty integrity, which will be ignored" => [
                [
                    "http://example.org/test#type=css&integrity=",
                ],
                '<link rel="stylesheet" href="http://example.org/test">',
            ],
            "CSS file with explicit defined type and an empty crossorigin, which will be ignored" => [
                [
                    "http://example.org/test#type=css&crossorigin=",
                ],
                '<link rel="stylesheet" href="http://example.org/test">',
            ],
            "CSS with explicit defined type and empty integrity + crossorigin, which both will be ignored" => [
                [
                    "http://example.org/test#type=css&integrity=&crossorigin=",
                ],
                '<link rel="stylesheet" href="http://example.org/test">',
            ],
            "CSS file with explicit defined type and integrity" => [
                [
                    "http://example.org/test#type=css&integrity=abc",
                ],
                '<link integrity="abc" rel="stylesheet" href="http://example.org/test">',
            ],
            "CSS file with explicit defined type and integrirty and crossorigin" => [
                [
                    "http://example.org/test#type=css&integrity=abc&crossorigin=anonymous",
                ],
                '<link integrity="abc" crossorigin="anonymous" rel="stylesheet" href="http://example.org/test">',
            ],
            "JS file with crossorigin" => [
                [
                    "http://example.org/test.js#crossorigin=use-credentials",
                ],
                '<script crossorigin="use-credentials" defer src="http://example.org/test.js"></script>',
            ],
            "JS file with explicit defined type and an empty integrity, which will be ignored" => [
                [
                    "http://example.org/test#type=js&integrity=",
                ],
                '<script defer src="http://example.org/test"></script>',
            ],
            "JS file with explicit defined type and an empty crossorigin, which will be ignored" => [
                [
                    "http://example.org/test#type=js&crossorigin=",
                ],
                '<script defer src="http://example.org/test"></script>',
            ],
            "JS with explicit defined type and empty integrity + crossorigin, which both will be ignored" => [
                [
                    "http://example.org/test#type=js&integrity=&crossorigin=",
                ],
                '<script defer src="http://example.org/test"></script>',
            ],
            "JS file with explicit defined type and query params" => [
                [
                    "http://www.example.org/de/_js/js/routing?callback=fos.Router.setData#type=js",
                ],
                '<script defer src="http://www.example.org/de/_js/js/routing?callback=fos.Router.setData"></script>',
            ],
        ];
    }


    /**
     * @dataProvider provideHttpImports
     *
     * @param array  $assets
     * @param string $expectedOutput
     *
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testHttpImports (array $assets, string $expectedOutput) : void
    {
        /**
         * @var AssetHtmlGenerator
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
         */
        [$generator] = $this->buildGenerator(false);


        $html = $generator->linkAssets($assets);
        self::assertSame($expectedOutput, $html);
    }


    /**
     *
     */
    public function testAllowCors () : void
    {
        /**
         * @var AssetHtmlGenerator
         * @var \PHPUnit_Framework_MockObject_MockObject $registry
         */
        [$generator] = $this->buildGenerator(false, true);

        self::assertSame(
            '<script crossorigin="anonymous" defer src="http://example.org/test.js"></script>',
            $generator->linkAssets(["http://example.org/test.js"])
        );
    }


    protected function buildGenerator (bool $isDebug, bool $allowCors = false)
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
            ->method("rewriteRelativeImports")
            ->willReturnArgument(1);

        $fileTypeRegistry = new FileTypeRegistry(new GenericFile(), new ServiceLocator([
            "js" => function () { return new JavaScriptFile(); },
            "css" => function () use ($importRewriter) { return new CssFile($importRewriter); },
        ]));

        $dependencyMapFactory = $this->getMockBuilder(DependencyMapFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dependencyMapFactory
            ->method("getDependencyMap")
            ->willReturn(new DependencyMap());

        $assetUrl
            ->method("generateUrl")
            ->willReturnCallback(function (Asset $asset) { return $asset->getAssetPath(); });

        $generator = new AssetHtmlGenerator($registry, $assetUrl, $fileTypeRegistry, $isDebug, $dependencyMapFactory, $allowCors);
        return [$generator, $registry, $assetUrl, $fileTypeRegistry];
    }
}
