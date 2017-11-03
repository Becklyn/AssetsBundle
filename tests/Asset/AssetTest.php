<?php

namespace Tests\Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Asset\Asset;
use PHPUnit\Framework\TestCase;


class AssetTest extends TestCase
{
    public function fileNameProvider ()
    {
        return [
            ["out", "a.js", "hash", "out/a.hash.js"],
            // slashes don't matter
            ["/out/", "a.js", "hash", "out/a.hash.js"],
            // invalid base64 chars are removed
            ["out", "a.js", "1+2/3=4", "out/a.1_2-34.js"],
            // hash is truncated
            ["out", "a.js", "1234567890123456789012345678901234567890", "out/a.12345678901234567890.js"],
            // only the basename of the file path is used
            ["out", "test/js/a.js", "hash", "out/a.hash.js"],
        ];
    }


    /**
     * @dataProvider fileNameProvider
     */
    public function testFileName (string $outDir, string $filePath, string $hash, string $expectedOutputFilePath)
    {
        $asset = new Asset($outDir, $filePath, $hash);
        self::assertSame($expectedOutputFilePath, $asset->getOutputFilePath());
    }
}
