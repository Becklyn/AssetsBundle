<?php

namespace Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Data\AssetFile;
use Becklyn\AssetsBundle\Data\AssetReference;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;


/**
 * Generates asset files from references
 */
class AssetFileGenerator
{
    /**
     * @var string
     */
    private $webDir;

    /**
     * @param string $rootDir
     */
    public function __construct (string $rootDir)
    {
        $this->webDir = dirname($rootDir) . "/web/";
    }



    /**
     * Generates an asset file from a reference
     *
     * @param AssetReference $reference
     *
     * @return AssetFile
     */
    public function generateAssetFile (AssetReference $reference) : AssetFile
    {
        $filePath = $this->webDir . ltrim($reference->getReference(), "/");

        if (!is_file($filePath))
        {
            throw new FileNotFoundException(sprintf(
                "The assets file '%s' could not be found at path '%s'.",
                $reference->getReference(),
                $filePath
            ));
        }

        $newFilename = sha1_file($filePath);
        $contentHash = base64_encode(hash_file(AssetFile::INTEGRITY_HASH_FUNCTION, $filePath, true));
        return new AssetFile($reference, $filePath, $contentHash, $newFilename);
    }
}
