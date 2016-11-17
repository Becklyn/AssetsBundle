<?php

namespace Becklyn\AssetsBundle\tests\Cache;

use Becklyn\AssetsBundle\Cache\FileCache;
use Becklyn\AssetsBundle\Data\AssetFile;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\tests\BaseTest;
use Symfony\Component\Filesystem\Filesystem;


class FileCacheTest extends BaseTest
{
    public function testAddToCache ()
    {
        $filesystem = self::getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache = new FileCache("/root/app", "assets", $filesystem);

        $assetFile = new AssetFile(new AssetReference("ref", "js"), "/full/path", "", "new-filename");

        $filesystem
            ->expects(self::once())
            ->method("copy")
            ->with(
                self::equalTo("/full/path"),
                self::equalTo("/root/web/assets/new-filename.js")
            );

        $cache->add($assetFile);
    }



    public function testClearCache ()
    {
        $filesystem = self::getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache = new FileCache("/root/app", "assets", $filesystem);

        $filesystem
            ->expects(self::once())
            ->method("remove")
            ->with(
                self::equalTo("/root/web/assets/")
            );

        $cache->clear();
    }
}
