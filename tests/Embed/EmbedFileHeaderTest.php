<?php

namespace Becklyn\AssetsBundle\tests\Embed;

use Becklyn\AssetsBundle\Embed\EmbedFileHeader;
use PHPUnit\Framework\TestCase;


class EmbedFileHeaderTest extends TestCase
{
    public function dataProviderFileHeader ()
    {
        return [
            [".js", "/\\*.*?\\*/"],
            ["css", "/\\*.*?\\*/"],
            ["svg", "<!--.*?-->"],
        ];
    }


    /**
     * @dataProvider dataProviderFileHeader
     */
    public function testFileHeader (string $extension, string $matcher)
    {
        $assetPath = "@namespace/file.{$extension}";
        $filePath = "/path/file.{$extension}";

        $embedFileHeader = new EmbedFileHeader();
        $header = $embedFileHeader->getFileHeader($assetPath, $filePath);

        self::assertContains($assetPath, $header);
        self::assertContains($filePath, $header);
        self::assertRegExp("~\\A{$matcher}\\Z~s", $header);
    }


    public function dataProviderEmptyFileHeader ()
    {
        return [
            ["invalid"],
            ["jpg"],
        ];
    }


    /**
     * @dataProvider dataProviderEmptyFileHeader
     */
    public function testEmptyFileHeader (string $extension)
    {
        $assetPath = "@namespace/file.{$extension}";
        $filePath = "/path/file.{$extension}";

        $embedFileHeader = new EmbedFileHeader();
        $header = $embedFileHeader->getFileHeader($assetPath, $filePath);

        self::assertEquals("", $header);
    }
}
