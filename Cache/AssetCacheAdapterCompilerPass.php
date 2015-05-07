<?php


namespace Becklyn\AssetsBundle\Cache;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AssetCacheAdapterCompilerPass implements CompilerPassInterface
{
    const CACHE_BUILDER_SERVICE_KEY = 'becklyn.assets.cache.cache_builder';


    /**
     * Scans all registered services for tagged CacheAdapter and registers it with the CacheBuilder
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::CACHE_BUILDER_SERVICE_KEY))
        {
            return;
        }

        // Get the definition of the CacheBuilder service itself
        $definition = $container->findDefinition(self::CACHE_BUILDER_SERVICE_KEY);

        // Get all CacheAdapter that are tagged for the CacheBuilder service
        foreach ($container->findTaggedServiceIds(self::CACHE_BUILDER_SERVICE_KEY) as $serviceKey => $tags)
        {
            $definition->addMethodCall(
                'addCacheAdapter',
                [
                    new Reference($serviceKey)
                ]
            );
        }
    }
}
