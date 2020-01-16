<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\File\Type\FileType;
use Becklyn\AssetsBundle\File\Type\GenericFile;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class FileTypeRegistry
{
    /**
     * @var GenericFile
     */
    private $genericFileType;


    /**
     * The file types mapped by file extension.
     *
     * @var ContainerInterface
     */
    private $specializedFileTypes;


    /**
     * @param ContainerInterface $specializedFileTypes the mapping of `extension => FileType` of all specialized file types
     */
    public function __construct (GenericFile $genericFileType, ContainerInterface $specializedFileTypes)
    {
        $this->genericFileType = $genericFileType;
        $this->specializedFileTypes = $specializedFileTypes;
    }


    /**
     * Returns the file type for an asset.
     */
    public function getFileType (Asset $asset) : FileType
    {
        return $this->getByFileExtension($asset->getFileType());
    }


    /**
     * Returns the file type by file extension.
     */
    public function getByFileExtension (string $extension) : FileType
    {
        try {
            return $this->specializedFileTypes->get($extension);
        }
        catch (ServiceNotFoundException $exception)
        {
            return $this->genericFileType;
        }
    }


    /**
     * Returns whether the given asset should be imported deferred.
     */
    public function importDeferred (Asset $asset) : bool
    {
        $fileType = $this->getFileType($asset);
        return $fileType->importDeferred();
    }
}
