<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Dependency;

use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Psr\Log\LoggerInterface;

class DependencyMapFactory
{
    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


    /**
     * @var array
     */
    private $dependencyFiles;


    /**
     * @var array
     */
    private $precompiledDependencyMap;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param NamespaceRegistry $namespaceRegistry
     * @param array             $dependencyFiles
     * @param array             $precompiledDependencyMap
     * @param bool              $isDebug
     * @param LoggerInterface   $logger
     */
    public function __construct (
        NamespaceRegistry $namespaceRegistry,
        array $dependencyFiles,
        array $precompiledDependencyMap,
        bool $isDebug,
        LoggerInterface $logger
    )
    {
        $this->namespaceRegistry = $namespaceRegistry;
        $this->dependencyFiles = $dependencyFiles;
        $this->precompiledDependencyMap = $precompiledDependencyMap;
        $this->isDebug = $isDebug;
        $this->logger = $logger;
    }


    /**
     * @return DependencyMap
     */
    public function getDependencyMap () : DependencyMap
    {
        if (!$this->isDebug)
        {
            return new DependencyMap($this->precompiledDependencyMap);
        }

        return $this->regenerateDependencyMap();
    }


    /**
     * Regenerates the dependency map.
     *
     * @return DependencyMap
     */
    private function regenerateDependencyMap () : DependencyMap
    {
        $loader = new DependencyLoader($this->namespaceRegistry, $this->logger);

        foreach ($this->dependencyFiles as $dependencyFile)
        {
            $loader->importFile($dependencyFile);
        }

        return new DependencyMap(
            $loader->getDependencyMap()
        );
    }
}
