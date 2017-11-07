<?php

namespace Tests\Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Asset\AssetGenerator;
use Becklyn\AssetsBundle\Processor\AssetProcessor;
use Becklyn\AssetsBundle\Processor\CssProcessor;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;


class AssetGeneratorTest extends TestCase
{
    /**
     * @var AssetGenerator
     */
    private $generator;


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
        $this->generator = new AssetGenerator(new ProcessorRegistry([]), $this->fixtures, "assets");

        $this->outDir = "{$this->fixtures}/assets";
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
        $expectedOutputFilePath = "assets/other/test/css/app2.zu_-RiyZqaqqHgSHa3Xv.css";
        $outputPath = "{$this->fixtures}/{$expectedOutputFilePath}";

        self::assertFileNotExists($outputPath);
        $asset = $this->generator->generateAsset("other/test/css/app2.css");
        self::assertFileExists($outputPath);

        self::assertSame("zu+/RiyZqaqqHgSHa3Xv6DI8rZax0+hDMV0WQk8xEZc=", $asset->getDigest());
        self::assertSame($expectedOutputFilePath, $asset->getOutputFilePath());

        self::assertFileEquals(
            "{$this->fixtures}/other/test/css/app2.css",
            $outputPath
        );
    }


    public function testBundleStripping ()
    {
        $asset = $this->generator->generateAsset("bundles/test/css/app.css");

        self::assertSame("U9K1d1vkqVvk8f9j82mik2tMIxI8E4C/QlXS/T6qgeE=", $asset->getDigest());
        self::assertSame("assets/test/css/app.U9K1d1vkqVvk8f9j82mi.css", $asset->getOutputFilePath());
    }


    public function testClear ()
    {
        $fs = new Filesystem();
        $fs->mkdir("{$this->outDir}/test");
        $fs->dumpFile("{$this->outDir}/test/a", "test");

        self::assertFileExists("{$this->outDir}/test/a");
        self::assertDirectoryExists($this->outDir);
        $this->generator->removeAllGeneratedFiles();
        self::assertDirectoryNotExists($this->outDir);
    }


    /**
     * @expectedException \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testMissingFile ()
    {
        $this->generator->generateAsset("missing");
    }


    public function testProcessorCalled ()
    {
        $processor = self::getMockBuilder(AssetProcessor::class)
            ->getMock();

        $generator = new AssetGenerator(new ProcessorRegistry([
            "css" => $processor
        ]), $this->fixtures, "assets");

        $processor
            ->expects(self::once())
            ->method("process");

        $generator->generateAsset("bundles/test/css/app.css");
    }


    public function testProcessorNotCalled ()
    {
        $processor = self::getMockBuilder(AssetProcessor::class)
            ->getMock();

        $generator = new AssetGenerator(new ProcessorRegistry([
            "css" => $processor
        ]), $this->fixtures, "assets");

        $processor
            ->expects(self::never())
            ->method("process");

        $generator->generateAsset("bundles/test/js/test.js");
    }
}
