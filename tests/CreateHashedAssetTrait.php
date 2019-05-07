<?php declare(strict_types=1);

namespace Tests\Becklyn\AssetsBundle;

use Becklyn\AssetsBundle\Asset\Asset;

trait CreateHashedAssetTrait
{
    /**
     * @param string $namespace
     * @param string $path
     * @param string $hash
     *
     * @return Asset
     */
    private function createdHashedAsset (string $namespace, string $path, string $hash) : Asset
    {
        $asset = new Asset($namespace, $path);
        $asset->setHash($hash);
        return $asset;
    }
}
