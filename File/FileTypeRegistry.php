<?php

namespace Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\File\Type\CssFile;
use Becklyn\AssetsBundle\File\Type\FileType;
use Becklyn\AssetsBundle\File\Type\GenericFile;
use Becklyn\AssetsBundle\File\Type\JavaScriptFile;
use Becklyn\AssetsBundle\File\Type\SvgFile;


class FileTypeRegistry
{
    /**
     * @var FileType
     */
    private $genericFileType;


    /**
     * The file types mapped by file extension
     *
     * @var array<string,FileType>
     */
    private $fileTypes = [];


    /**
     *
     * @param FileType $genericFileType
     * @param array    $specializedFileTypes    the mapping of `extension => FileType` of all specialized file types
     */
    public function __construct (FileType $genericFileType, array $specializedFileTypes = [])
    {
        $this->genericFileType = $genericFileType;
        $this->fileTypes = $specializedFileTypes;
    }


    /**
     * @param Asset $asset
     * @return FileType
     */
    public function getFileType (Asset $asset) : FileType
    {
        return $this->fileTypes[$asset->getFileType()] ?? $this->genericFileType;
    }


    /**
     * Returns whether the given asset should be imported deferred
     *
     * @param Asset $asset
     * @return bool
     */
    public function importDeferred (Asset $asset) : bool
    {
        $fileType = $this->getFileType($asset);
        return $fileType->importDeferred();
    }
}
