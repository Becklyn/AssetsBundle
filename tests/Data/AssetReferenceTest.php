<?php

namespace Becklyn\AssetsBundle\tests\Data;

use Becklyn\AssetsBundle\Data\AssetReference;


class AssetReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Becklyn\AssetsBundle\Exception\InvalidAssetTypeException
     */
    public function testInvalidType ()
    {
        new AssetReference("a.test", "test");
    }
}
