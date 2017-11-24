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
     * @var string[]
     */
    private $entries;


    /**
     * @var string
     */
    private $projectDir;


    /**
     * @var EntryNamespaces
     */
    private $namespaceRegistry;


    /**
     *
     * @param string          $projectDir
     * @param EntryNamespaces $namespaceRegistry
     */
    public function __construct (string $projectDir, EntryNamespaces $namespaceRegistry)
    {
        $this->projectDir = $projectDir;
        $this->namespaceRegistry = $namespaceRegistry;
    }


    /**
     * @return string[]
     */
    public function findAssets () : array
    {
        $files = [];

        foreach ($this->namespaceRegistry as $namespace => $dir)
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
