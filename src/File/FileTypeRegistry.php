<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\File\Type\FileType;
use Becklyn\AssetsBundle\File\Type\GenericFile;

class FileTypeRegistry
{
    /**
     * @var GenericFile
     */
    private $genericFileType;


    /**
     * The file types mapped by file extension.
     *
     * @var array<string,FileType>
     */
    private $fileTypes = [];


    /**
     * @param GenericFile $genericFileType
     * @param array       $specializedFileTypes the mapping of `extension => FileType` of all specialized file types
     */
    public function __construct (GenericFile $genericFileType, array $specializedFileTypes = [])
    {
        $this->genericFileType = $genericFileType;
        $this->fileTypes = $specializedFileTypes;
    }


    /**
     * Returns the file type for an asset.
     *
     * @param Asset $asset
     *
     * @return FileType
     */
    public function getFileType (Asset $asset) : FileType
    {
        return $this->getByFileExtension($asset->getFileType());
    }


    /**
     * Returns the file type by file extension.
     *
     * @param string $extension
     *
     * @return FileType
     */
    public function getByFileExtension (string $extension) : FileType
    {
        return $this->fileTypes[$extension] ?? $this->genericFileType;
    }


    /**
     * Returns whether the given asset should be imported deferred.
     *
     * @param Asset $asset
     *
     * @return bool
     */
    public function importDeferred (Asset $asset) : bool
    {
        $fileType = $this->getFileType($asset);
        return $fileType->importDeferred();
    }
}
