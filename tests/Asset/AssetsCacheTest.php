<?php

namespace Tests\Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetStorage;
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

        $cachedAsset = new Asset("test", "test.js");
        $cachedAsset->setHash("hash");
        $asset = new Asset("test", "test.js");

        $cacheItem
            ->method("get")
            ->willReturn([
                "@test/test.js" => $asset,
            ]);

        $assetsCache = new AssetsCache($cachePool);
        self::assertSame($asset, $assetsCache->get($asset));
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
        $asset = new Asset("test", "test.js");
        self::assertNull($assetsCache->get($asset));
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

        // don't make assumptions about the internals here
        $cacheItem
            ->expects(self::once())
            ->method("set");

        $cachePool
            ->expects(self::once())
            ->method("save")
            ->with($cacheItem);

        $assetsCache = new AssetsCache($cachePool);
        $asset = new Asset("test", "test.js");
        self::assertNull($assetsCache->get($asset));
        $assetsCache->add($asset);
        self::assertSame($asset, $assetsCache->get($asset));
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

        $asset = new Asset("test", "test.js");

        // prime cache
        $cacheItem
            ->method("isHit")
            ->willReturn(true);

        $cacheItem
            ->method("get")
            ->willReturn([
                "@test/test.js" => $asset,
            ]);

        $assetsCache = new AssetsCache($cachePool);
        // check that item is in cache
        self::assertSame($asset, $assetsCache->get($asset));

        // clear cache
        $assetsCache->clear();

        self::assertNull($assetsCache->get($asset));
    }
}
