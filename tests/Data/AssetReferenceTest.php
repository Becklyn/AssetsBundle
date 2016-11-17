<?php

namespace Becklyn\AssetsBundle\tests\Data;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\tests\BaseTest;


class AssetReferenceTest extends BaseTest
{
    /**
     * @group asset-reference
     * @expectedException \Becklyn\AssetsBundle\Exception\InvalidAssetTypeException
     */
    public function testInvalidType ()
    {
        new AssetReference("a.test", "test");
    }
}
