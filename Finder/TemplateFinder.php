<?php

namespace Becklyn\AssetsBundle\Finder;

use Symfony\Component\Finder\Finder;


/**
 * Finds all template files in a given directory
 */
class TemplateFinder
{
    /**
     * @param string $directory
     *
     * @return \SplFileInfo[]
     */
    public function findInDirectory (string $directory) : array
    {
        $result = [];

        $finder = new Finder();
        $finder
            ->in($directory)
            ->files()
            ->name('*.html.twig')
            ->contains('/{%\s+?(javascripts|stylesheets) /i')
            ->followLinks()
            ->ignoreUnreadableDirs();

        /** @var \SplFileInfo $file */
        foreach ($finder as $file)
        {
            $result[] = $file;
        }

        return $result;
    }
}
