<?php

namespace Becklyn\AssetsBundle\Finder;

use Symfony\Component\Finder\Finder;


/**
 * Finds all assets in the bundles directory of the project
 */
class AssetsFinder
{
    const BUNDLES_DIR = "bundles";


    /**
     *
     * @param string $projectDir
     */
    public function __construct (string $projectDir)
    {
        $this->searchDir = "{$projectDir}/public";
    }


    /**
     * @return string[]
     */
    public function findAssets () : array
    {
        $finder = new Finder();

        $finder
            ->files()
            ->in("{$this->searchDir}/" . self::BUNDLES_DIR)
            ->name("~\.(js|css)$~");

        $files = [];

        foreach ($finder as $file)
        {
            $files[] = self::BUNDLES_DIR . "/{$file->getRelativePathname()}";
        }

        return $files;
    }
}
