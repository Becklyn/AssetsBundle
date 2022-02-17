<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle;

use Becklyn\AssetsBundle\DependencyInjection\BecklynAssetsExtension;
use Becklyn\AssetsBundle\File\Type\SpecializedFileType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BecklynAssetsBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function getContainerExtension () : ?ExtensionInterface
    {
        return new BecklynAssetsExtension();
    }


    /**
     * @inheritDoc
     */
    public function build (ContainerBuilder $container) : void
    {
        $container
            ->registerForAutoconfiguration(SpecializedFileType::class)
            ->addTag("becklyn_assets.file_type");
    }
}
