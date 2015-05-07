<?php


namespace Becklyn\AssetsBundle\Cache\Adapter;


use Becklyn\AssetsBundle\Service\AssetConfigurationService;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class AssetFilesystemCacheAdapter implements AssetCacheAdapterInterface
{
    /**
     * @var string
     */
    private $cacheTableFilePath;


    /**
     * @var array
     */
    private $tempAssetsCache = [];


    /**
     * AssetFilesystemCache constructor.
     *
     * @param AssetConfigurationService $configurationService
     */
    public function __construct (AssetConfigurationService $configurationService)
    {
        $this->cacheTableFilePath = $configurationService->getCacheTableFilePath();
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
        if (!$fileSystem->exists($this->cacheTableFilePath))
        {
            return $defaultValue;
        }

        require_once $this->cacheTableFilePath;

        $assetsCache = new \AssetsCache();
        $result = $assetsCache->getCachedAssetPath($key);

        if (is_null($result))
        {
            return $defaultValue;
        }

        return $result;
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
     * @param array $options
     *
     * @return void
     */
    public function build (array $options = [])
    {
        $override = isset($options['override']) && $options['override'];
        $clearOutput = isset($options['clear']) && $options['clear'];

        $fileSystem = new Filesystem();

        // Skip if the Assets Cache Table already exists and the user doesn't want to override it
        if ($fileSystem->exists($this->cacheTableFilePath) && !$override)
        {
            return;
        }

        $cacheContents = "<?php

class AssetsCache
{

    private \$cache = [
";

        foreach ($this->tempAssetsCache as $key => $value)
        {
            $cacheContents .= "        '$key' => '$value',\n";
        }

        $cacheContents .= "    ];

    public function getCachedAssetPath (\$key)
    {
        if (!isset(\$this->cache[\$key]))
        {
            return null;
        }

        return \$this->cache[\$key];
    }
}
?>";

        if ($clearOutput)
        {
            $this->clearOutputDirectory();
        }

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
        if (!$fileSystem->exists(dirname($this->cacheTableFilePath)))
        {
            $fileSystem->mkdir(dirname($this->cacheTableFilePath));
        }

        $fileSystem->dumpFile($this->cacheTableFilePath, $contents);
    }


    /**
     * Removes the Asset Cache Table output directory
     */
    private function clearOutputDirectory ()
    {
        $fileSystem = new Filesystem();

        if ($fileSystem->exists(dirname($this->cacheTableFilePath)))
        {
            try
            {
                $fileSystem->remove(dirname($this->cacheTableFilePath));
            }
            catch (IOException $e)
            {
                // Swallow any exceptions
            }
        }
    }
}
