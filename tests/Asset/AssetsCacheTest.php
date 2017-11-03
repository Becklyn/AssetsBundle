<?php

namespace Tests\Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetGenerator;
use Becklyn\AssetsBundle\Asset\AssetsCache;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;


class AssetsCacheTest extends TestCase
{
    private function prepareCache ()
    {
        $cachePool = self::getMockBuilder(CacheItemPoolInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheItem = self::getMockBuilder(CacheItemInterface::class)
            ->getMock();

        $cachePool
            ->method("getItem")
            ->willReturn($cacheItem);

        $generator = self::getMockBuilder(AssetGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [$cachePool, $cacheItem, $generator];
    }


    public function testCacheInitialization ()
    {
        /** @type \PHPUnit_Framework_MockObject_MockObject $cachePool */
        /** @type \PHPUnit_Framework_MockObject_MockObject $cacheItem */
        [$cachePool, $cacheItem, $generator] = $this->prepareCache();

        $cacheItem
            ->method("isHit")
            ->willReturn(true);

        $asset = new Asset("test", "test", "test");

        $cacheItem
            ->method("get")
            ->willReturn([
                "test.js" => $asset,
            ]);

        $assetsCache = new AssetsCache($cachePool, $generator);
        self::assertSame($asset, $assetsCache->get("test.js"));
    }


    public function testAutomaticGeneration ()
    {
        /** @type \PHPUnit_Framework_MockObject_MockObject $cachePool */
        /** @type \PHPUnit_Framework_MockObject_MockObject $cacheItem */
        /** @type \PHPUnit_Framework_MockObject_MockObject $generator */
        [$cachePool, $cacheItem, $generator] = $this->prepareCache();

        $cacheItem
            ->method("isHit")
            ->willReturn(false);

        $asset = new Asset("test", "test", "test");

        $generator
            ->expects(self::once())
            ->method("generateAsset")
            ->with($this->equalTo("test.js"))
            ->willReturn($asset);

        $assetsCache = new AssetsCache($cachePool, $generator);
        self::assertSame($asset, $assetsCache->get("test.js"));
    }


    public function testClear ()
    {
        /** @type \PHPUnit_Framework_MockObject_MockObject $cachePool */
        /** @type \PHPUnit_Framework_MockObject_MockObject $cacheItem */
        /** @type \PHPUnit_Framework_MockObject_MockObject $generator */
        [$cachePool, $cacheItem, $generator] = $this->prepareCache();

        $cacheItem
            ->method("isHit")
            ->willReturn(true);

        $asset = new Asset("test", "test", "test");

        $cacheItem
            ->method("get")
            ->willReturn([
                "test.js" => $asset,
            ]);

        $assetsCache = new AssetsCache($cachePool, $generator);
        // check that item is in cache
        self::assertSame($asset, $assetsCache->get("test.js"));

        // check that actual cache clearer is called
        $generator
            ->expects(self::once())
            ->method("removeAllGeneratedFiles");

        // clear cache
        $assetsCache->clear();

        // check that new item is generated if cache is called again
        $asset2 = new Asset("test", "test", "test");
        $generator
            ->expects(self::once())
            ->method("generateAsset")
            ->with(self::equalTo("test.js"))
            ->willReturn($asset2);
        self::assertSame($asset2, $assetsCache->get("test.js"));
    }
}
