<?php

namespace Becklyn\AssetsBundle\Cache;

use Symfony\Component\Filesystem\Filesystem;


class AssetCache
{
    /**
     * @var string
     */
    private $assetsCacheTable = "assets_cache_table.php";


    /**
     * @var string[]
     */
    private $assetsCache = null;


    /**
     * @var array
     */
    private $tempAssetsCache = [];


    /**
     * AssetCache constructor.
     *
     * @param string $cacheDir
     */
    public function __construct (string $cacheDir)
    {
        $this->assetsCacheTable = "{$cacheDir}/assets_cache_table.php";
    }



    /**
     * Adds the key-value pair to the temporary cache and upon calling build() to the file system
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $override
     *
     * @return void
     */
    public function add ($key, $value, $override = false)
    {
        if (in_array($key, $this->tempAssetsCache) && !$override)
        {
            return;
        }

        $this->tempAssetsCache[$key] = $value;
    }


    /**
     * Retrieves the cached value by key
     *
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed|null
     */
    public function get ($key, $defaultValue = null)
    {
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($this->assetsCacheTable))
        {
            return $defaultValue;
        }

        if (null === $this->assetsCache)
        {
            $this->assetsCache = require_once $this->assetsCacheTable;
        }

        return $this->assetsCache[$key] ?? $defaultValue;
    }


    /**
     * Checks whether the given key is already cached on the file system (Assets Cache Table)
     *
     * @param string $key
     *
     * @return bool
     */
    public function isCached ($key)
    {
        return $this->get($key) !== null;
    }


    /**
     * Writes the cached data to the file system
     *
     * @return void
     */
    public function build ()
    {
        $cacheContents = "<?php

return [
";

        foreach ($this->tempAssetsCache as $key => $value)
        {
            $cacheContents .= "    '$key' => '$value',\n";
        }

        $cacheContents .= "];

?>";

        $this->writeCacheFile($cacheContents);
    }


    /**
     * Writes the content to the file system (Assets Cache Table)
     *
     * @param string $contents
     */
    private function writeCacheFile ($contents)
    {
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists(dirname($this->assetsCacheTable)))
        {
            $fileSystem->mkdir(dirname($this->assetsCacheTable));
        }

        $fileSystem->dumpFile($this->assetsCacheTable, $contents);
    }
}
