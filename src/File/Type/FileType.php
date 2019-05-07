<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;

abstract class FileType
{
    /**
     * Processes the file content for development.
     *
     * @param Asset  $asset
     * @param string $filePath
     * @param string $fileContent
     *
     * @return string
     */
    public function processForDev (Asset $asset, string $filePath, string $fileContent) : string
    {
        return $fileContent;
    }


    /**
     * Processes the file content for production.
     *
     * @param Asset  $asset
     * @param string $fileContent
     *
     * @return string
     */
    public function processForProd (Asset $asset, string $fileContent) : string
    {
        return $fileContent;
    }


    /**
     * Returns whether the file should be loaded deferred.
     *
     * @return bool
     */
    public function importDeferred () : bool
    {
        return false;
    }


    /**
     * Returns the link format to link to this file type from HTML.
     *
     * Is passed to sprintf() with the following parameters:
     *      1: the url
     *      2: integrity HTML attribute
     *
     * @return string|null
     */
    public function getHtmlLinkFormat () : ?string
    {
        return null;
    }


    /**
     * Flag whether the file name of the dumped file should contain the hash.
     *
     * @return bool
     */
    public function shouldIncludeHashInFileName () : bool
    {
        return true;
    }


    /**
     * Returns whether the file type is compressible via HZIP.
     *
     * @return bool
     */
    public function shouldBeGzipCompressed () : bool
    {
        return false;
    }
}
