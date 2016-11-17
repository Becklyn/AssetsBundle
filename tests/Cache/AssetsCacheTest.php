<?php

namespace Becklyn\AssetsBundle\tests\Cache;

use Becklyn\AssetsBundle\Cache\AssetsCache;
use Becklyn\AssetsBundle\Cache\FileCache;
use Becklyn\AssetsBundle\Cache\MappingCache;
use Becklyn\AssetsBundle\Data\AssetFile;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\File\AssetFileGenerator;
use Becklyn\AssetsBundle\tests\BaseTest;


class AssetsCacheTest extends BaseTest
{
    public function testAddCorrect ()
    {
        // create mocks
        $fileGenerator = self::getMockBuilder(AssetFileGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileCache = self::getMockBuilder(FileCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mappingCache = self::getMockBuilder(MappingCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reference = new AssetReference("", "js");
        $file = new AssetFile($reference, "/path/file", "hash", "name");

        // preparation of return values
        $fileGenerator
            ->method("generateAssetFile")
            ->willReturn($file);

        $mappingCache
            ->method("get")
            ->willReturn(null);

        // expect calls
        $mappingCache
            ->expects(self::once())
            ->method("add")
            ->with(
                self::equalTo($file)
            );

        $fileCache
            ->expects(self::once())
            ->method("add")
            ->with(
                self::equalTo($file)
            );

        // execute
        $assetsCache = new AssetsCache($fileGenerator, $fileCache, $mappingCache);
        $assetsCache->add($reference);
    }


    /**
     * @expectedException Becklyn\AssetsBundle\Exception\InvalidCacheEntryException
     */
    public function testAddDuplicateWithDifferingHash ()
    {
        // create mocks
        $fileGenerator = self::getMockBuilder(AssetFileGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileCache = self::getMockBuilder(FileCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mappingCache = self::getMockBuilder(MappingCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reference = new AssetReference("", "js");
        $file = new AssetFile($reference, "/path/file", "hash", "name");

        // preparation of return values
        $fileGenerator
            ->method("generateAssetFile")
            ->willReturn($file);

        $mappingCache
            ->method("get")
            ->willReturn(new AssetFile(new AssetReference("", "js"), "/path/file", "otherhash", "name"));

        // execute
        $assetsCache = new AssetsCache($fileGenerator, $fileCache, $mappingCache);
        $assetsCache->add($reference);
    }


    public function testAddDuplicateWithSameHash ()
    {
        // create mocks
        $fileGenerator = self::getMockBuilder(AssetFileGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileCache = self::getMockBuilder(FileCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mappingCache = self::getMockBuilder(MappingCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reference = new AssetReference("", "js");
        $file = new AssetFile($reference, "/path/file", "hash", "name");

        // preparation of return values
        $fileGenerator
            ->method("generateAssetFile")
            ->willReturn($file);

        $mappingCache
            ->method("get")
            ->willReturn(new AssetFile(new AssetReference("", "js"), "/path/file", "hash", "name"));


        // expect calls
        $mappingCache
            ->expects(self::once())
            ->method("add")
            ->with(
                self::equalTo($file)
            );

        $fileCache
            ->expects(self::once())
            ->method("add")
            ->with(
                self::equalTo($file)
            );

        // execute
        $assetsCache = new AssetsCache($fileGenerator, $fileCache, $mappingCache);
        $assetsCache->add($reference);
    }



    public function testClear ()
    {
        // create mocks
        $fileGenerator = self::getMockBuilder(AssetFileGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileCache = self::getMockBuilder(FileCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mappingCache = self::getMockBuilder(MappingCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        // expect calls
        $fileCache
            ->expects(self::once())
            ->method("clear");

        $mappingCache
            ->expects(self::once())
            ->method("clear");

        // execute
        $assetsCache = new AssetsCache($fileGenerator, $fileCache, $mappingCache);
        $assetsCache->clear();
    }
}
