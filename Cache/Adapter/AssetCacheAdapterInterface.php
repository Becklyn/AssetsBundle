<?php


namespace Becklyn\AssetsBundle\Cache\Adapter;


interface AssetCacheAdapterInterface
{
    /**
     * Adds the key-value pair to the cache
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $override
     *
     * @return mixed
     */
    public function add ($key, $value, $override = false);


    /**
     * Retrieves a value by key from the cache
     *
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get ($key, $defaultValue = null);


    /**
     * Checks whether the given key is already cached
     *
     * @param string $key
     *
     * @return bool
     */
    public function isCached ($key);


    /**
     * Optionally performs the last step that may be required to persist the cache
     *
     * @param array $options
     *
     * @return mixed
     */
    public function build (array $options = []);
}
