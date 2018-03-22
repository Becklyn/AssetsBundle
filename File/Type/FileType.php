<?php

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;


abstract class FileType
{
    /**
     * Adds the file header
     *
     * @param Asset  $asset
     * @param string $filePath
     * @param string $fileContent
     * @return string
     */
    public function addFileHeader (Asset $asset, string $filePath, string $fileContent) : string
    {
        return $fileContent;
    }


    /**
     * Processes the file content for production
     *
     * @param Asset  $asset
     * @param string $fileContent
     * @return string
     */
    public function processForProd (Asset $asset, string $fileContent) : string
    {
        return $fileContent;
    }


    /**
     * Returns whether the file should be loaded deferred
     *
     * @return bool
     */
    public function importDeferred () : bool
    {
        return false;
    }
}
