<?php

namespace Becklyn\AssetsBundle\tests\Cache\MappingCache;

use Becklyn\AssetsBundle\Cache\MappingCache\MappingCacheIO;
use Becklyn\AssetsBundle\tests\BaseTest;
use Symfony\Component\Filesystem\Filesystem;


class MappingCacheIOTest extends BaseTest
{
    public function testWrite ()
    {
        $filesystem = self::getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $io = new MappingCacheIO("/cache/", $filesystem);

        $filesystem
            ->expects(self::once())
            ->method("dumpFile")
            ->with(
                self::equalTo("/cache/becklyn/assets/assets_mapping.php"),
                self::stringContains('hai')
            );

        $io->write(["oh" => "hai"]);
    }



    public function testLoadValid ()
    {
        $filesystem = self::getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $io = new MappingCacheIO($this->getFixturesDirectory("/cache/"), $filesystem, "valid.php");
        $data = $io->load();

        self::assertContains("item", $data);
        self::assertArrayHasKey("example", $data);
        self::assertCount(1, $data);
    }



    public static function dataProviderInvalidLoads ()
    {
        return [
            ["invalid.php"],
            ["empty.php"],
            ["file_missing.php"],
        ];
    }



    /**
     * @dataProvider dataProviderInvalidLoads
     */
    public function testLoadInValid ($filename)
    {
        $filesystem = self::getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $io = new MappingCacheIO($this->getFixturesDirectory("/cache/"), $filesystem, $filename);
        $data = $io->load();

        self::assertCount(0, $data);
    }
}
