<?php

namespace Becklyn\AssetsBundle\tests\Path;

use Becklyn\AssetsBundle\Cache\AssetCache;
use Becklyn\AssetsBundle\Data\AssetFile;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\CachedReference;
use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\tests\BaseTest;


class PathGeneratorTest extends BaseTest
{
    /**
     * @group path-generator
     */
    public function testGetDoesNotUseCacheInDebug ()
    {
        $assetReference = new AssetReference("a.js", AssetReference::TYPE_JAVASCRIPT);
        $assetCache = self::getMockBuilder(AssetCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $assetCache->expects($this->never())
            ->method("get")
            ->withAnyParameters();

        $isDebug = true;

        $pathGenerator = new PathGenerator($assetCache, $isDebug);

        $result = $pathGenerator->getDisplayAssetReference($assetReference);

        self::assertSame($result, $assetReference);
    }


    /**
     * @group path-generator
     */
    public function testGetUsesCacheInProd ()
    {
        $assetReference = new AssetReference("a.js", AssetReference::TYPE_JAVASCRIPT);
        $cachedReferenced = new CachedReference("a.js", "hash", AssetFile::INTEGRITY_HASH_FUNCTION);

        $assetCache = self::getMockBuilder(AssetCache::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $assetCache->expects($this->once())
            ->method("get")
            ->with($assetReference)
            ->willReturn($cachedReferenced);

        $isProd = false;

        $pathGenerator = new PathGenerator($assetCache, $isProd);

        $result = $pathGenerator->getDisplayAssetReference($assetReference);

        self::assertSame($result, $cachedReferenced);
    }
}
