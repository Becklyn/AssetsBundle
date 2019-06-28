<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Data\AssetEmbed;
use Becklyn\AssetsBundle\Exception\NotEmbeddableFileTypeException;
use Becklyn\HtmlBuilder\Node\HtmlElement;

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
     * Returns the element for embedding this file type.
     *
     * @throws NotEmbeddableFileTypeException
     *
     * @return HtmlElement
     */
    public function buildElementForEmbed (AssetEmbed $embed) : HtmlElement
    {
        throw new NotEmbeddableFileTypeException();
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
