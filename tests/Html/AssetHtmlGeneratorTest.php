<?php

namespace Tests\Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\RouterInterface;


class AssetHtmlGeneratorTest extends TestCase
{
    private $js = [];
    private $css = [];


    protected function setUp ()
    {
        $this->js = [
            new Asset("out/js", "first.js", "j1"),
            new Asset("out/js", "second.js", "j2"),
        ];

        $this->css = [
            new Asset("out/css", "first.css", "c1"),
            new Asset("out/css", "second.css", "c2"),
        ];
    }


    protected function buildGenerator (bool $isDebug)
    {
        $registry = self::getMockBuilder(AssetsRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $router = self::getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $generator = new AssetHtmlGenerator($registry, $router, $isDebug);
        return [$generator, $registry, $router];
    }


    public function testDebugJS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $router
         */
        [$generator, $registry, $router] = $this->buildGenerator(true);

        // the registry must not be called in debug mode
        $registry
            ->expects(self::never())
            ->method("get");

        $router
            ->expects(self::exactly(2))
            ->method("generate")
            ->withConsecutive(
                ["becklyn_assets_embed", ["path" => \rawurlencode("@bundles/a/first.js")]],
                ["becklyn_assets_embed", ["path" => \rawurlencode("@bundles/b/second.js")]]
            )
            ->willReturnCallback(function ($path, $params)
            {
                return "{$path}={$params["path"]}";
            });

        $html = $generator->linkAssets("js", ["@bundles/a/first.js", "@bundles/b/second.js"]);
        self::assertEquals(
            '<script defer src="becklyn_assets_embed=%40bundles%2Fa%2Ffirst.js"></script><script defer src="becklyn_assets_embed=%40bundles%2Fb%2Fsecond.js"></script>',
            $html
        );
    }


    public function testDebugCSS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $router
         */
        [$generator, $registry, $router] = $this->buildGenerator(true);

        // the registry must not be called in debug mode
        $registry
            ->expects(self::never())
            ->method("get");

        $router
            ->expects(self::exactly(2))
            ->method("generate")
            ->withConsecutive(
                ["becklyn_assets_embed", ["path" => \rawurlencode("@bundles/a/first.css")]],
                ["becklyn_assets_embed", ["path" => \rawurlencode("@bundles/b/second.css")]]
            )
            ->willReturnCallback(function ($path, $params)
            {
                return "{$path}={$params["path"]}";
            });

        $html = $generator->linkAssets("css", ["@bundles/a/first.css", "@bundles/b/second.css"]);
        self::assertEquals(
            '<link rel="stylesheet" href="becklyn_assets_embed=%40bundles%2Fa%2Ffirst.css"><link rel="stylesheet" href="becklyn_assets_embed=%40bundles%2Fb%2Fsecond.css">',
            $html
        );
    }


    public function testProductionJS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $router
         */
        [$generator, $registry, $router] = $this->buildGenerator(false);

        $registry
            ->method("get")
            ->willReturnCallback(
                function ($url)
                {
                    return new Asset("out/", $url, "hash");
                }
            );

        $router
            ->method("generate")
            ->willReturnArgument(0);

        $html = $generator->linkAssets("js", ["a/first.js", "b/second.js"]);
        self::assertEquals(
            '<script defer src="out/first.hash.js" integrity="sha256-hash"></script><script defer src="out/second.hash.js" integrity="sha256-hash"></script>',
            $html
        );
    }


    public function testProductionCSS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $router
         */
        [$generator, $registry, $router] = $this->buildGenerator(false);

        $registry
            ->method("get")
            ->willReturnCallback(
                function ($url)
                {
                    return new Asset("out/", $url, "hash");
                }
            );

        $router
            ->method("generate")
            ->willReturnArgument(0);

        $html = $generator->linkAssets("css", ["a/first.css", "b/second.css"]);
        self::assertEquals(
            '<link rel="stylesheet" href="out/first.hash.css" integrity="sha256-hash"><link rel="stylesheet" href="out/second.hash.css" integrity="sha256-hash">',
            $html
        );
    }
}
