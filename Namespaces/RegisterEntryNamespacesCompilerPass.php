<?php

namespace Becklyn\AssetsBundle\Namespaces;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * A helper compiler pass that automatically registers the given asset namespaces
 */
class RegisterEntryNamespacesCompilerPass implements CompilerPassInterface
{
    /**
     * Mapping from namespace => path.
     * {@see EntryNamespaces::addNamespace()}
     *
     * @var array
     */
    private $mapping;

    /**
     * @param array $mapping
     */
    public function __construct (array $mapping)
    {
        $this->mapping = $mapping;
    }


    /**
     * @inheritdoc
     */
    public function process (ContainerBuilder $container)
    {
        $entryNamespaces = $container->getDefinition(NamespaceRegistry::class);

        foreach ($this->mapping as $namespace => $path)
        {
            $entryNamespaces->addMethodCall("addNamespace", [$namespace, $path]);
        }
    }
}
