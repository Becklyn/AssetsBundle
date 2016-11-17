<?php

namespace Becklyn\AssetsBundle\Cache\MappingCache;

use Symfony\Component\Filesystem\Filesystem;


/**
 * Handles all IO operations for the mapping cache
 */
class MappingCacheIO
{
    const CACHE_DIR = "/becklyn/assets/";

    /**
     * @var string
     */
    private $cacheFile;


    /**
     * @var Filesystem
     */
    private $filesystem;



    /**
     * @param string     $cacheDir
     * @param Filesystem $filesystem
     * @param string     $cacheFileName
     */
    public function __construct (string $cacheDir, Filesystem $filesystem, string $cacheFileName = "assets_mapping.php")
    {
        $this->cacheFile = rtrim($cacheDir, "/") . self::CACHE_DIR . $cacheFileName;
        $this->filesystem = $filesystem;
    }



    /**
     * Writes the cache file
     *
     * @param array $data
     */
    public function write (array $data)
    {
        $cacheContents = '<?php return ' . var_export($data, true) . ';';
        $this->filesystem->dumpFile($this->cacheFile, $cacheContents);
    }



    /**
     * Loads the data from the cache file
     *
     * @return array
     */
    public function load () : array
    {
        $assetsCache = null;

        if (is_file($this->cacheFile))
        {
            $assetsCache = @include $this->cacheFile;
        }

        return is_array($assetsCache)
            ? $assetsCache
            : [];
    }
}
