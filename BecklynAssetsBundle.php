<?php

namespace Becklyn\AssetsBundle;

use Becklyn\AssetsBundle\DependencyInjection\BecklynAssetsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class BecklynAssetsBundle extends Bundle
{
    public function getContainerExtension ()
    {
        return new BecklynAssetsExtension();
    }

}
