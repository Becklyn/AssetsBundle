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
    private function prepare ()
    {
        $cachePool = $this->getMockBuilder(CacheItemPoolInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheItem = $this->getMockBuilder(CacheItemInterface::class)
            ->getMock();

        $cachePool
            ->method("getItem")
            ->willReturn($cacheItem);

        return [$cachePool, $cacheItem];
    }


    public function testCacheInitialization ()
    {
        /**
         * @type \PHPUnit_Framework_MockObject_MockObject|CacheItemPoolInterface $cachePool
         * @type \PHPUnit_Framework_MockObject_MockObject $cacheItem
         */
        [$cachePool, $cacheItem] = $this->prepare();

        $cacheItem
            ->method("isHit")
            ->willReturn(true);

        $asset = new Asset("test", "test", "test");

        $cacheItem
            ->method("get")
            ->willReturn([
                "test.js" => $asset,
            ]);

        $assetsCache = new AssetsCache($cachePool);
        self::assertSame($asset, $assetsCache->get("test.js"));
    }


    public function testEmptyInitialization ()
    {
        /**
         * @type \PHPUnit_Framework_MockObject_MockObject|CacheItemPoolInterface $cachePool
         * @type \PHPUnit_Framework_MockObject_MockObject $cacheItem
         */
        [$cachePool, $cacheItem] = $this->prepare();

        $cacheItem
            ->method("isHit")
            ->willReturn(true);

        $cacheItem
            ->method("get")
            ->willReturn([]);

        $assetsCache = new AssetsCache($cachePool);
        self::assertNull($assetsCache->get("test.js"));
    }


    public function testAdd ()
    {
        /**
         * @type \PHPUnit_Framework_MockObject_MockObject|CacheItemPoolInterface $cachePool
         * @type \PHPUnit_Framework_MockObject_MockObject $cacheItem
         */
        [$cachePool, $cacheItem] = $this->prepare();

        $cacheItem
            ->method("isHit")
            ->willReturn(false);

        $asset = new Asset("test", "test", "test");

        // don't make assumptions about the internals here
        $cacheItem
            ->expects(self::once())
            ->method("set");

        $cachePool
            ->expects(self::once())
            ->method("save")
            ->with($cacheItem);

        $assetsCache = new AssetsCache($cachePool);
        self::assertNull($assetsCache->get("test.js"));
        $assetsCache->add("test.js", $asset);
        self::assertSame($asset, $assetsCache->get("test.js"));
    }


    public function testClear ()
    {
        /**
         * @type \PHPUnit_Framework_MockObject_MockObject|CacheItemPoolInterface $cachePool
         * @type \PHPUnit_Framework_MockObject_MockObject $cacheItem
         */
        [$cachePool, $cacheItem] = $this->prepare();

        $cacheItem
            ->expects(self::once())
            ->method("set")
            ->with([]);

        $cachePool
            ->expects(self::once())
            ->method("save")
            ->with($cacheItem);

        $asset = new Asset("test", "test", "test");

        // prime cache
        $cacheItem
            ->method("isHit")
            ->willReturn(true);

        $cacheItem
            ->method("get")
            ->willReturn([
                "test.js" => $asset,
            ]);

        $assetsCache = new AssetsCache($cachePool);
        // check that item is in cache
        self::assertSame($asset, $assetsCache->get("test.js"));

        // clear cache
        $assetsCache->clear();

        self::assertNull($assetsCache->get("test.js"));
    }
}
