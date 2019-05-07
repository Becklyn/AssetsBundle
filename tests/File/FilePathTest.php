<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File;

use PHPUnit\Framework\TestCase;

class FilePathTest extends TestCase
{
    /**
     * @return array
     */
    public function provideResolvePath () : array
    {
        return [
            ["a/b/c.jpg", "d.png", "/a/b/d.png"],
            ["a/b/c.jpg", "d/e.png", "/a/b/d/e.png"],
            ["a/b/c.jpg", "/d/e/f.png", "/d/e/f.png"],

            ["a/b/c.jpg", "f.png", "/a/b/f.png"],
            ["a/b/c.jpg", "../f.png", "/a/f.png"],
            ["a/b/c.jpg", "../../f.png", "/f.png"],

            // invalid, but just stops at the top
            ["a/b/c.jpg", "../../../f.png", "/f.png"],

            // dots in the middle are kept
            ["a/b/c.jpg", "d/../e/./f.png", "/a/b/d/../e/./f.png"],

            // without files
            ["a/b/c.jpg", "/", "/"],
            ["a/b/c.jpg", "e/", "/a/b/e"],
        ];
    }


    /**
     * Tests that paths are correctly resolved.
     *
     * @dataProvider provideResolvePath
     *
     * @param string $sourcePath
     * @param string $relativePathToTarget
     * @param string $expectedResult
     */
    public function testResolvePath (string $sourcePath, string $relativePathToTarget, string $expectedResult) : void
    {
        $filePath = new FilePath();
        $resolved = $filePath->resolvePath($sourcePath, $relativePathToTarget);

        self::assertSame($expectedResult, $resolved, "{$sourcePath} -> {$relativePathToTarget}");
    }
}
