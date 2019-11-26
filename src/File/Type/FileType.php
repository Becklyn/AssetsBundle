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
     */
    public function processForDev (Asset $asset, string $filePath, string $fileContent) : string
    {
        return $fileContent;
    }


    /**
     * Processes the file content for production.
     */
    public function processForProd (Asset $asset, string $fileContent) : string
    {
        return $fileContent;
    }


    /**
     * Returns whether the file should be loaded deferred.
     */
    public function importDeferred () : bool
    {
        return false;
    }


    /**
     * Returns the element for embedding this file type.
     *
     * @throws NotEmbeddableFileTypeException
     */
    public function buildElementForEmbed (AssetEmbed $embed) : HtmlElement
    {
        throw new NotEmbeddableFileTypeException();
    }


    /**
     * Flag whether the file name of the dumped file should contain the hash.
     */
    public function shouldIncludeHashInFileName () : bool
    {
        return true;
    }


    /**
     * Returns whether the file type is compressible via HZIP.
     */
    public function shouldBeGzipCompressed () : bool
    {
        return false;
    }
}
