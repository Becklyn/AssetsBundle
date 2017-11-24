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
    private $dirs;


    /**
     *
     * @param string[] $entries
     */
    public function __construct (string $projectDir, array $entries)
    {
        $this->dirs = [];

        foreach ($entries as $entry)
        {
            $dir = "{$projectDir}/" . ltrim($entry, "/");

            if (\is_dir($projectDir))
            {
                $this->dirs[] = $dir;
            }
        }
    }


    /**
     * @return string[]
     */
    public function findAssets () : array
    {
        if (empty($this->dirs))
        {
            return [];
        }

        $finder = new Finder();

        $finder
            ->files()
            ->followLinks()
            ->in($this->dirs);


        return \array_map(
            function (SplFileInfo $file)
            {
                return $file->getRelativePathname();
            },
            \iterator_to_array($finder)
        );
    }
}
