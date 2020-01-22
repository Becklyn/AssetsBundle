<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Dependency;

use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class DependencyMapFactory
{
    private const CACHE_KEY = "becklyn_assets.dependencies_map";

    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * The in-memory cached dependency map
     *
     * @var DependencyMap|null
     */
    private $map;


    /**
     * @var CacheInterface
     */
    private $cache;


    /**
     */
    public function __construct (
        NamespaceRegistry $namespaceRegistry,
        CacheInterface $cache,
        bool $isDebug,
        LoggerInterface $logger
    )
    {
        $this->namespaceRegistry = $namespaceRegistry;
        $this->isDebug = $isDebug;
        $this->logger = $logger;
        $this->cache = $cache;
    }


    /**
     */
    public function getDependencyMap () : DependencyMap
    {
        if (null === $this->map)
        {
            $this->map = !$this->isDebug
                ? $this->cache->get(
                    self::CACHE_KEY,
                    function () { return $this->regenerateDependencyMap(); }
                )
                : $this->regenerateDependencyMap();
        }

        return $this->map;
    }


    /**
     * Regenerates the dependency map.
     */
    private function regenerateDependencyMap () : DependencyMap
    {
        // loader will automatically try to load the dependencies file for every namespace
        $loader = new DependencyLoader($this->namespaceRegistry, $this->logger);
        return new DependencyMap($loader->getDependencyMap());
    }
}
