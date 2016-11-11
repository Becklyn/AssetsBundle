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
     * @return string[]
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

        foreach ($finder as $file)
        {
            /** @var \SplFileInfo $file */
            $result[] = $file->getPathname();
        }

        return $result;
    }
}
