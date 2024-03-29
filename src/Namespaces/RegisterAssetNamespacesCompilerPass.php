<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Namespaces;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * A helper compiler pass that automatically registers the given asset namespaces.
 */
class RegisterAssetNamespacesCompilerPass implements CompilerPassInterface
{
    /**
     * Mapping from namespace => path.
     * {@see EntryNamespaces::addNamespace()}.
     */
    private array $mapping;


    public function __construct (array $mapping)
    {
        $this->mapping = $mapping;
    }


    /**
     * @inheritdoc
     */
    public function process (ContainerBuilder $container) : void
    {
        $entryNamespaces = $container->getDefinition(NamespaceRegistry::class);

        foreach ($this->mapping as $namespace => $path)
        {
            $entryNamespaces->addMethodCall("addNamespace", [$namespace, $path]);
        }
    }
}
