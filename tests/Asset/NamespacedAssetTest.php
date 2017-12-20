<?php

namespace Becklyn\AssetsBundle\tests\Asset;

use Becklyn\AssetsBundle\Asset\NamespacedAsset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use PHPUnit\Framework\TestCase;


class NamespacedAssetTest extends TestCase
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
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testCreateFromPath (string $path, string $expectedNamespace, string $expectedPath)
    {
        $asset = NamespacedAsset::createFromFullPath($path);
        self::assertSame($expectedNamespace, $asset->getNamespace());
        self::assertSame($expectedPath, $asset->getPath());
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
        ];
    }


    /**
     * @dataProvider provideFailedCreateFromPath
     * @expectedException Becklyn\AssetsBundle\Exception\AssetsException
     *
     * @param string $path
     * @throws \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testFailedCreateFromPath (string $path)
    {
        $a = NamespacedAsset::createFromFullPath($path);
        \var_dump($a);
    }



}
