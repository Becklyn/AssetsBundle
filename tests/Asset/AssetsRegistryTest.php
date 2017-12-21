<?php

namespace Tests\Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetGenerator;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;
use PHPUnit\Framework\TestCase;


class AssetsRegistryTest extends TestCase
{
    private function prepare ()
    {
        $cache = $this->getMockBuilder(AssetsCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $generator = $this->getMockBuilder(AssetGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [$cache, $generator];
    }


    public function testAutomaticGeneration ()
    {
        /**
         * @type \PHPUnit_Framework_MockObject_MockObject|AssetsCache $cache
         * @type \PHPUnit_Framework_MockObject_MockObject|AssetGenerator $generator
         */
        [$cache, $generator] = $this->prepare();

        $asset = new Asset("test", "test", "test");

        $generator
            ->expects(self::once())
            ->method("generateAsset")
            ->with($this->equalTo("test.js"))
            ->willReturn($asset);

        $assetsCache = new AssetsRegistry($cache, $generator, new ProcessorRegistry([]));
        self::assertSame($asset, $assetsCache->get("test.js"));
    }


    public function testClear ()
    {
        /**
         * @type \PHPUnit_Framework_MockObject_MockObject|AssetsCache $cache
         * @type \PHPUnit_Framework_MockObject_MockObject|AssetGenerator $generator
         */
        [$cache, $generator] = $this->prepare();

        // check that cache is cleared
        $cache
            ->expects(self::once())
            ->method("clear");

        // check that actual cache clearer is called
        $generator
            ->expects(self::once())
            ->method("removeAllGeneratedFiles");

        // clear cache
        $registry = new AssetsRegistry($cache, $generator, new ProcessorRegistry([]));
        $registry->clear();
    }
}
