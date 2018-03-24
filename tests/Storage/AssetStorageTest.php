<?php

namespace Tests\Becklyn\AssetsBundle\Storage;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\File\FileLoader;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\File\Type\GenericFile;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Becklyn\AssetsBundle\Storage\AssetStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;


class AssetStorageTest extends TestCase
{
    /**
     * @var AssetStorage
     */
    private $storage;


    /**
     * @var string
     */
    private $fixtures;


    /**
     * @var string
     */
    private $outDir;


    /**
     * @inheritdoc
     */
    public function setUp ()
    {
        $this->fixtures = dirname(__DIR__) . "/fixtures/public";
        $this->outDir = "{$this->fixtures}/out";

        $namespaces = new NamespaceRegistry($this->fixtures, [
            "bundles" => "bundles",
            "other" => "other",
        ]);


        $this->storage = new AssetStorage(
            new FileLoader($namespaces, new FileTypeRegistry([], new GenericFile())),
            $this->outDir,
            "assets"
        );

        $fs = new Filesystem();
        $fs->remove($this->outDir);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown ()
    {
        $fs = new Filesystem();
        $fs->remove($this->outDir);
    }


    public function testGenerate ()
    {
        $expectedOutputFilePath = "other/test/css/app2.zu+_RiyZqaqqHgSHa3Xv.css";
        $outputPath = "{$this->outDir}/assets/{$expectedOutputFilePath}";

        self::assertFileNotExists($outputPath);
        $asset = $this->storage->import(new Asset("other", "test/css/app2.css"));
        self::assertFileExists($outputPath);

        self::assertSame("zu+/RiyZqaqqHgSHa3Xv6DI8rZax0+hDMV0WQk8xEZc=", $asset->getHash());
        self::assertSame($expectedOutputFilePath, $asset->getDumpFilePath());

        self::assertFileEquals(
            "{$this->fixtures}/other/test/css/app2.css",
            $outputPath
        );
    }


    public function testBundleStripping ()
    {
        $asset = $this->storage->import(new Asset("bundles", "test/css/app.css"));

        self::assertSame("U9K1d1vkqVvk8f9j82mik2tMIxI8E4C/QlXS/T6qgeE=", $asset->getHash());
        self::assertSame("bundles/test/css/app.U9K1d1vkqVvk8f9j82mi.css", $asset->getDumpFilePath());
    }


    public function testClear ()
    {
        $fs = new Filesystem();
        $assetsDir = "{$this->outDir}/assets";
        $fs->mkdir("{$assetsDir}/test");
        $fs->dumpFile("{$assetsDir}/test/a", "test");

        self::assertFileExists("{$assetsDir}/test/a");
        self::assertDirectoryExists($assetsDir);
        $this->storage->removeAllStoredFiles();
        self::assertDirectoryNotExists($assetsDir);
    }
}
