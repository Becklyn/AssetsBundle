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
     * @var string
     */
    private $publicPath;


    /**
     * @param string $publicPath
     */
    public function __construct (string $publicPath)
    {
        $this->publicPath = rtrim($publicPath, "/");
    }


    /**
     * @return string[]
     */
    public function findAssets () : array
    {
        $finder = new Finder();

        $finder
            ->files()
            ->followLinks()
            ->in("{$this->publicPath}/" . self::BUNDLES_DIR);

        $files = [];

        foreach ($finder as $file)
        {
            $files[] = self::BUNDLES_DIR . "/{$file->getRelativePathname()}";
        }

        return $files;
    }
}
