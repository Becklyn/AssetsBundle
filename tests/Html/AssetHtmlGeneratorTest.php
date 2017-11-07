<?php

namespace Tests\Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Html\AssetHtmlGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;


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

        $packages = self::getMockBuilder(Packages::class)
            ->disableOriginalConstructor()
            ->getMock();

        $generator = new AssetHtmlGenerator($registry, $packages, $isDebug);
        return [$generator, $registry, $packages];
    }


    public function testDebugJS ()
    {
        [$generator, $registry, $packages] = $this->buildGenerator(true);

        // the registry must not be called in debug mode
        $registry
            ->expects(self::never())
            ->method("get");

        $packages
            ->expects(self::exactly(2))
            ->method("getUrl")
            ->withConsecutive(
                ["a/first.js"],
                ["b/second.js"]
            )
            ->willReturnArgument(0);

        $html = $generator->linkAssets("js", ["a/first.js", "b/second.js"]);
        self::assertEquals(
            '<script src="a/first.js"></script><script src="b/second.js"></script>',
            $html
        );
    }


    public function testDebugCSS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $packages
         */
        [$generator, $registry, $packages] = $this->buildGenerator(true);

        // the registry must not be called in debug mode
        $registry
            ->expects(self::never())
            ->method("get");

        $packages
            ->expects(self::exactly(2))
            ->method("getUrl")
            ->withConsecutive(
                ["a/first.css"],
                ["b/second.css"]
            )
            ->willReturnArgument(0);

        $html = $generator->linkAssets("css", ["a/first.css", "b/second.css"]);
        self::assertEquals(
            '<link rel="stylesheet" href="a/first.css"><link rel="stylesheet" href="b/second.css">',
            $html
        );
    }


    public function testProductionJS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $packages
         */
        [$generator, $registry, $packages] = $this->buildGenerator(false);

        $registry
            ->method("get")
            ->willReturnCallback(
                function ($url)
                {
                    return new Asset("out/", $url, "hash");
                }
            );

        $packages
            ->method("getUrl")
            ->willReturnArgument(0);

        $html = $generator->linkAssets("js", ["a/first.js", "b/second.js"]);
        self::assertEquals(
            '<script src="out/first.hash.js" integrity="sha256-hash"></script><script src="out/second.hash.js" integrity="sha256-hash"></script>',
            $html
        );
    }


    public function testProductionCSS ()
    {
        /**
         * @type AssetHtmlGenerator $generator
         * @type \PHPUnit_Framework_MockObject_MockObject $registry
         * @type \PHPUnit_Framework_MockObject_MockObject $packages
         */
        [$generator, $registry, $packages] = $this->buildGenerator(false);

        $registry
            ->method("get")
            ->willReturnCallback(
                function ($url)
                {
                    return new Asset("out/", $url, "hash");
                }
            );

        $packages
            ->method("getUrl")
            ->willReturnArgument(0);

        $html = $generator->linkAssets("css", ["a/first.css", "b/second.css"]);
        self::assertEquals(
            '<link rel="stylesheet" href="out/first.hash.css" integrity="sha256-hash"><link rel="stylesheet" href="out/second.hash.css" integrity="sha256-hash">',
            $html
        );
    }
}
