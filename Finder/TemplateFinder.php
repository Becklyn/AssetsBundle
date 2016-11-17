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
        try
        {
            $result = [];

            $finder = new Finder();
            $finder
                ->in($directory)
                ->files()
                ->name('*.html.twig')
                ->contains('/{%\s+?(javascripts|stylesheets) /i')
                ->notPath('tests')
                ->followLinks()
                ->ignoreUnreadableDirs();

            /** @var \SplFileInfo $file */
            foreach ($finder as $file)
            {
                $result[] = $file->getPathname();
            }

            return $result;
        }
        catch (\InvalidArgumentException $e)
        {
            // silently ignore missing directories
            return [];
        }
    }
}
