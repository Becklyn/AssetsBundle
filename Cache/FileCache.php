<?php

namespace Becklyn\AssetsBundle\Cache;

use Becklyn\AssetsBundle\Data\AssetFile;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Handles the writing of cached asset files
 */
class FileCache
{
    /**
     * @var string
     */
    private $assetsPath;


    /**
     * @var Filesystem
     */
    private $filesystem;



    /**
     * @param string     $rootPath
     * @param string     $relativeAssetsDir the relative path to the assets cache directory (relative to /web/)
     * @param Filesystem $filesystem
     */
    public function __construct (string $rootPath, string $relativeAssetsDir, Filesystem $filesystem)
    {
        $this->assetsPath = dirname($rootPath) . "/web/" . trim($relativeAssetsDir, "/") . "/";
        $this->filesystem = $filesystem;
    }



    /**
     * Adds the given file to the cache
     *
     * @param AssetFile $file
     */
    public function add (AssetFile $file)
    {
        $this->filesystem->copy(
            $file->getFilePath(),
            $this->assetsPath . $file->getNewFileName()
        );
    }



    /**
     * Clears all cached assets
     */
    public function clear ()
    {
        $this->filesystem->remove($this->assetsPath);
    }
}
