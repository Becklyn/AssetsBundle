<?php


namespace Becklyn\AssetsBundle\Cache;


use Becklyn\AssetsBundle\Cache\Adapter\AssetCacheAdapterInterface;

class AssetCacheBuilder
{
    /**
     * @var AssetCacheAdapterInterface[]
     */
    private $cacheAdapters;


    /**
     * Registers the CacheAdapter for further usage
     *
     * @param AssetCacheAdapterInterface $cacheAdapter
     */
    public function addCacheAdapter (AssetCacheAdapterInterface $cacheAdapter)
    {
        $this->cacheAdapters[] = $cacheAdapter;
    }


    /**
     * Caches the given key-value pair with all registered CacheAdapter
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $override
     */
    public function add ($key, $value, $override = false)
    {
        foreach ($this->cacheAdapters as $cacheAdapter)
        {
            $cacheAdapter->add($key, $value, $override);
        }
    }


    /**
     * Retrieves the stored value from the first CacheAdapter that returns a value
     *
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get ($key, $defaultValue = null)
    {
        foreach ($this->cacheAdapters as $cacheAdapter)
        {
            $value = $cacheAdapter->get($key, $defaultValue);
            if (!is_null($value))
            {
                return $value;
            }
        }

        return $defaultValue;
    }


    /**
     * Determines whether or not the given key is cached in any of the registered CacheAdapters
     *
     * @param string $key
     *
     * @return bool
     */
    public function isCached ($key)
    {
        foreach ($this->cacheAdapters as $cacheAdapter)
        {
            if ($cacheAdapter->isCached($key))
            {
                return true;
            }
        }

        return false;
    }


    /**
     * Calls all registered CacheAdapters to optionally persist their data
     *
     * @param array $options
     */
    public function build (array $options = [])
    {
        foreach ($this->cacheAdapters as $cacheAdapter)
        {
            $cacheAdapter->build($options);
        }
    }
}
