<?php

namespace Becklyn\AssetsBundle\tests\File;

use Becklyn\AssetsBundle\Entry\EntryNamespaces;
use Becklyn\AssetsBundle\File\FileLoader;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;
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
        $entryNamespaces = new EntryNamespaces($this->fixtures, [
            "bundles" => "public/bundles"
        ]);

        $processorRegistry = $this->getMockBuilder(ProcessorRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loader = new FileLoader($entryNamespaces, $processorRegistry);
    }


    public function dataProviderValid ()
    {
        return [
            ["@bundles/test/css/app.css", "{$this->fixtures}/public/bundles/test/css/app.css"],
            ["@bundles/test/js/test.js", "{$this->fixtures}/public/bundles/test/js/test.js"],
        ];
    }


    /**
     * @dataProvider dataProviderValid
     *
     * @param string $assetPath
     * @param string $expectedFile
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testValid (string $assetPath, string $expectedFile)
    {
        self::assertStringEqualsFile($expectedFile, $this->loader->loadFile($assetPath));
    }


    public function dataProviderInvalid ()
    {
        return [
            ["@invalid/test.js"],
            ["@bundles/test/js/doesnt_exist.js"],
            ["@Invalid/test.js"],
        ];
    }


    /**
     * @dataProvider dataProviderInvalid
     * @param string $assetPath
     *
     * @expectedException \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testInvalid (string $assetPath)
    {
        $this->loader->loadFile($assetPath);
    }
}
