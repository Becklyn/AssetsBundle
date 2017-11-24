<?php

namespace Becklyn\AssetsBundle\Finder;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;


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
     *
     * @param string[] $entries
     */
    public function __construct (string $projectDir, array $entries)
    {
        $this->projectDir = $projectDir;
        $this->entries = $entries;
    }


    /**
     * @return string[]
     */
    public function findAssets () : array
    {
        if (empty($this->entries))
        {
            return [];
        }

        $files = [];

        foreach ($this->entries as $namespace => $entry)
        {
            $dir = "{$this->projectDir}/" . ltrim($entry, "/");

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
