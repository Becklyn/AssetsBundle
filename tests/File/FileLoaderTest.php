<?php

namespace Tests\Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\File\FileLoader;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\File\Type\GenericFile;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use PHPUnit\Framework\TestCase;


class FileLoaderTest extends TestCase
{
    /**
     * @var FileLoader
     */
    private $loader;


    /**
     * @var string
     */
    private $fixtures = __DIR__ . "/../fixtures";


    protected function setUp ()
    {
        $entryNamespaces = new NamespaceRegistry($this->fixtures, [
            "bundles" => "public/bundles"
        ]);

        $fileTypes = new FileTypeRegistry([], new GenericFile());

        $this->loader = new FileLoader($entryNamespaces, $fileTypes);
    }


    public function dataProviderValid ()
    {
        return [
            [new Asset("bundles", "test/css/app.css"), "{$this->fixtures}/public/bundles/test/css/app.css"],
            [new Asset("bundles", "test/js/test.js"), "{$this->fixtures}/public/bundles/test/js/test.js"],
        ];
    }


    /**
     * @dataProvider dataProviderValid
     *
     * @param Asset  $asset
     * @param string $expectedFile
     */
    public function testValid (Asset $asset, string $expectedFile)
    {
        self::assertStringEqualsFile($expectedFile, $this->loader->loadFile($asset, FileLoader::MODE_UNTOUCHED));
    }


    public function dataProviderInvalid ()
    {
        return [
            [new Asset("invalid", "test.js")],
            [new Asset("bundles", "test/js/doesnt_exist.js")],
            [new Asset("Invalid", "test.js")],
        ];
    }


    /**
     * @dataProvider dataProviderInvalid
     * @param Asset $asset
     *
     * @expectedException \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testInvalid (Asset $asset)
    {
        $this->loader->loadFile($asset, FileLoader::MODE_UNTOUCHED);
    }
}
