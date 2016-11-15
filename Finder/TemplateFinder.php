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

        try
        {
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
        }
        catch (\InvalidArgumentException $e)
        {
            // Swallow exception since we're returning an empty result
        }

        return $result;
    }
}
