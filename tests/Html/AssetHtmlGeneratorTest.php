<?php

namespace Tests\Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use Becklyn\AssetsBundle\Url\AssetUrl;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
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
        $registry = $this->getMockBuilder(AssetsRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetUrl = $this->getMockBuilder(AssetUrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetUrl
            ->method("generateUrl")
            ->willReturnArgument(0);

        $generator = new AssetHtmlGenerator($registry, $assetUrl, $isDebug);
        return [$generator, $registry, $assetUrl];
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

        $assetUrl
            ->expects(self::exactly(2))
            ->method("generateUrl")
            ->withConsecutive(
                ["@bundles/a/first.js"],
                ["@bundles/b/second.js"]
            );

        $html = $generator->linkAssets("js", ["@bundles/a/first.js", "@bundles/b/second.js"]);
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

        $assetUrl
            ->expects(self::exactly(2))
            ->method("generateUrl")
            ->withConsecutive(
                ["@bundles/a/first.css"],
                ["@bundles/b/second.css"]
            );

        $html = $generator->linkAssets("css", ["@bundles/a/first.css", "@bundles/b/second.css"]);
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
         * @type \PHPUnit_Framework_MockObject_MockObject $assetUrl
         */
        [$generator, $registry, $assetUrl] = $this->buildGenerator(false);

        $registry
            ->method("get")
            ->willReturnCallback(
                function ($url)
                {
                    return new Asset("out/", $url, "hash");
                }
            );

        $html = $generator->linkAssets("js", ["@a/first.js", "@b/second.js"]);
        self::assertContains('integrity="', $html);
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

        $html = $generator->linkAssets("css", ["@a/first.css", "@b/second.css"]);
        self::assertContains('integrity="', $html);
    }
}
