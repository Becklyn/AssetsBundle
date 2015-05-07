<?php

namespace Becklyn\AssetsBundle;

use Becklyn\AssetsBundle\Cache\AssetCacheAdapterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BecklynAssetsBundle extends Bundle
{
    public function build (ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AssetCacheAdapterCompilerPass());
    }
}
