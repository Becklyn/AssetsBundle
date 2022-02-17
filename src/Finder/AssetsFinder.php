<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Finder;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Symfony\Component\Finder\Finder;

/**
 * Finds all assets in the bundles directory of the project.
 */
class AssetsFinder
{
    private NamespaceRegistry $namespaces;


    public function __construct (NamespaceRegistry $namespaces)
    {
        $this->namespaces = $namespaces;
    }


    /**
     * Finds all assets in the namespaces.
     *
     * @return Asset[]
     */
    public function findAssets () : array
    {
        $assets = [];

        foreach ($this->namespaces as $namespace => $dir)
        {
            if (!\is_dir($dir))
            {
                continue;
            }

            $finder = new Finder();

            $finder
                ->files()
                ->followLinks()
                ->in($dir);

            foreach ($finder as $foundFile)
            {
                $assets[] = new Asset($namespace, $foundFile->getRelativePathname());
            }
        }

        return $assets;
    }
}
