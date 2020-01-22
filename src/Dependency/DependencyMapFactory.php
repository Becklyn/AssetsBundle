<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Dependency;

use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class DependencyMapFactory
{
    private const DEPENDENCY_MAP_RELATIVE_PATH = "/js/_dependencies.json";
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
                    self::DEPENDENCY_MAP_RELATIVE_PATH,
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
        $loader = new DependencyLoader($this->namespaceRegistry, $this->logger);

        foreach ($this->namespaceRegistry as $namespace => $path)
        {
            if (\is_file($path . self::DEPENDENCY_MAP_RELATIVE_PATH))
            {
                $loader->importFile("@{$namespace}" . self::DEPENDENCY_MAP_RELATIVE_PATH);
            }
        }

        return new DependencyMap($loader->getDependencyMap());
    }
}
