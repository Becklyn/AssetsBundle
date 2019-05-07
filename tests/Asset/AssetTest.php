<?php declare(strict_types=1);

namespace Tests\Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Asset\Asset;
use PHPUnit\Framework\TestCase;

class AssetTest extends TestCase
{
    /**
     * @return array
     */
    public function provideCreateFromPath ()
    {
        return [
            ["@bundle/a/test.js", "bundle", "a/test.js"],
            ["@bundle/a/test.js/", "bundle", "a/test.js"],
            ["@bundle//a/test.js", "bundle", "a/test.js"],
            ["@bundle//a/test.js/", "bundle", "a/test.js"],
            ["@bundle123/a/test.js", "bundle123", "a/test.js"],
            ["@bundle_123/a/test.js", "bundle_123", "a/test.js"],
        ];
    }


    /**
     * @dataProvider provideCreateFromPath
     *
     * @param string $path
     * @param string $expectedNamespace
     * @param string $expectedPath
     *
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testCreateFromPath (string $path, string $expectedNamespace, string $expectedPath) : void
    {
        $asset = Asset::createFromAssetPath($path);
        self::assertSame($expectedNamespace, $asset->getNamespace());
        self::assertSame($expectedPath, $asset->getFilePath());
    }


    /**
     * @return array
     */
    public function provideFailedCreateFromPath ()
    {
        return [
            ["a/test.js"],
            ["bundle/a/test.js/"],
            ["@bundle/"],
            ["@bundle"],
            ["@123bundle/a.js"],
            ["@MaydTestBundle/a/test.js"],
            ["@_test/a/test.js"],
        ];
    }


    /**
     * @dataProvider provideFailedCreateFromPath
     * @expectedException \Becklyn\AssetsBundle\Exception\AssetsException
     *
     * @param string $path
     *
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testFailedCreateFromPath (string $path) : void
    {
        Asset::createFromAssetPath($path);
    }


    /**
     * Tests, that no other properties are copied over when generating a relative asset.
     */
    public function testRelativeCreationResetsProperties () : void
    {
        $asset = new Asset("namespace", "path.jpg");
        $asset->setHash("my-hash");

        self::assertSame("my-hash", $asset->getHash());
        $relativeAsset = $asset->getRelativeAsset("test.jpg");
        self::assertSame("my-hash", $asset->getHash());
        self::assertNull($relativeAsset->getHash());
    }
}
