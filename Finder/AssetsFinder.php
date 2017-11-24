<?php

namespace Becklyn\AssetsBundle\Finder;

use Becklyn\AssetsBundle\Entry\EntryNamespaces;
use Symfony\Component\Finder\Finder;


/**
 * Finds all assets in the bundles directory of the project
 */
class AssetsFinder
{
    /**
     * @var EntryNamespaces
     */
    private $namespaces;


    /**
     * @param EntryNamespaces $namespaces
     */
    public function __construct (EntryNamespaces $namespaces)
    {
        $this->namespaces = $namespaces;
    }


    /**
     * @return string[]
     */
    public function findAssets () : array
    {
        $files = [];

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
                $files[] = "@{$namespace}/{$foundFile->getRelativePathname()}";
            }
        }

        return $files;
    }
}
