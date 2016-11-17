<?php

namespace Becklyn\AssetsBundle\tests\Data;

use Becklyn\AssetsBundle\Data\AssetFile;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\tests\BaseTest;


class AssetFileTest extends BaseTest
{
    /**
     * @group asset-file
     * @group javascript
     */
    public function testFilenameJavaScript ()
    {
        $assetFile = new AssetFile(new AssetReference("a.js", AssetReference::TYPE_JAVASCRIPT), "a/b/c", "hash", "new-file-name");

        self::assertSame("new-file-name.js", $assetFile->getNewFileName());
    }


    /**
     * @group asset-file
     * @group stylesheet
     */
    public function testFilenameStylesheet ()
    {
        $assetFile = new AssetFile(new AssetReference("a.css", AssetReference::TYPE_STYLESHEET), "a/b/c", "hash", "new-file-name");

        self::assertSame("new-file-name.css", $assetFile->getNewFileName());
    }
}
