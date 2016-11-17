<?php

namespace Becklyn\AssetsBundle\tests\Cache;

use Becklyn\AssetsBundle\Cache\MappingCache;
use Becklyn\AssetsBundle\Cache\MappingCache\MappingCacheIO;
use Becklyn\AssetsBundle\Data\AssetFile;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\tests\BaseTest;


class MappingCacheTest extends BaseTest
{
    private function generateAssetFile () : AssetFile
    {
        return new AssetFile(
            new AssetReference("reference", "js"),
            "/original/path",
            "content-hash",
            "file-name"
        );
    }


    public function testGetExistingItem ()
    {
        $cacheIO = self::getMockBuilder(MappingCacheIO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheIO
            ->method("load")
            ->willReturn([
                "reference" => [
                    "fileName" => "file-name.js",
                    "contentHash" => "content-hash",
                    // the test should still work when the hash function is changed
                    "hashAlgorithm" => AssetFile::INTEGRITY_HASH_FUNCTION,
                ]
            ]);

        $mappingCache = new MappingCache("/assets/", $cacheIO);
        $item = $mappingCache->get("reference");

        self::assertEquals("assets/file-name.js", $item->getRelativeUrl());
        self::assertEquals("content-hash", $item->getContentHash());
        self::assertEquals(AssetFile::INTEGRITY_HASH_FUNCTION, $item->getHashFunction());
    }


    public function testGetMissingItem ()
    {
        $cacheIO = self::getMockBuilder(MappingCacheIO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheIO
            ->method("load")
            ->willReturn([]);

        $mappingCache = new MappingCache("/assets/", $cacheIO);
        $item = $mappingCache->get("missing-reference");

        self::assertNull($item);
    }



    public function testAddItem ()
    {
        $cacheIO = self::getMockBuilder(MappingCacheIO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mappingCache = new MappingCache("/assets/", $cacheIO);
        $file = $this->generateAssetFile();

        $cacheIO
            ->expects(self::once())
            ->method("write")
            ->with(
                self::equalTo([
                    "reference" => [
                        "fileName" => "file-name.js",
                        "contentHash" => "content-hash",
                        // the test should still work when the hash function is changed
                        "hashAlgorithm" => AssetFile::INTEGRITY_HASH_FUNCTION,
                    ]
                ])
            );

        $mappingCache->add($file);
    }



    public function testAddWithExistingItem ()
    {
        $cacheIO = self::getMockBuilder(MappingCacheIO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $existingData = [
            "existing" => [
                "fileName" => "existing.js",
                "contentHash" => "hash",
                "hashAlgorithm" => "1"
            ]
        ];

        $cacheIO
            ->method("load")
            ->willReturn($existingData);

        $mappingCache = new MappingCache("/assets/", $cacheIO);
        $file = $this->generateAssetFile();

        $cacheIO
            ->expects(self::once())
            ->method("write")
            ->with(
                self::equalTo(array_replace($existingData, [
                    "reference" => [
                        "fileName" => "file-name.js",
                        "contentHash" => "content-hash",
                        // the test should still work when the hash function is changed
                        "hashAlgorithm" => AssetFile::INTEGRITY_HASH_FUNCTION,
                    ]
                ]))
            );

        $mappingCache->add($file);
    }



    public function testClearCache ()
    {
        $cacheIO = self::getMockBuilder(MappingCacheIO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mappingCache = new MappingCache("/assets/", $cacheIO);

        $cacheIO
            ->expects(self::once())
            ->method("write")
            ->with(
                self::equalTo([])
            );

        $mappingCache->clear();
    }
}
