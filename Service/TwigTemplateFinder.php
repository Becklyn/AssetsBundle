<?php


namespace Becklyn\AssetsBundle\Service;


use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class TwigTemplateFinder extends ContainerAware
{
    /**
     * Temporary dumb, cache for all loaded Bundles
     *
     * @var array
     */
    private $bundlePathsCache = [];


    /**
     * Temporary dumb, cache for Bundles => Template Paths
     *
     * @var array
     */
    private $bundleTemplatePathsCache = [];


    /**
     * @var Kernel
     */
    private $kernel;


    /**
     * @var string
     */
    private $appRootDirectory;


    /**
     * TwigTemplateFinder constructor.
     *
     * @param Kernel $kernel
     */
    public function __construct (Kernel $kernel)
    {
        $this->kernel           = $kernel;
        $this->appRootDirectory = dirname($kernel->getRootDir());
    }


    /**
     * Searches all loaded bundles for .twig templates
     *
     * @return string[]
     */
    public function getAllTemplatePaths ()
    {
        // If we have a cached version available we use it
        if (!empty($this->bundleTemplatePathsCache))
        {
            return $this->bundleTemplatePathsCache;
        }

        $result = [];
        foreach ($this->getBundlePaths() as $bundleName => $bundlePath)
        {
            // TODO: Think about whether it makes sense to introduce a max depth filter by making use of the depth() method:
            // http://symfony.com/doc/current/components/finder.html#directory-depth

            $finder = new Finder();
            $finder
                ->files()
                ->name('*.twig')
                ->contains('/{%\s*?(javascripts|stylesheets)/i')
                ->followLinks()
                ->ignoreUnreadableDirs()
                ->in($bundlePath);

            foreach ($finder as $file)
            {
                /** @var SplFileInfo $file */
                $result[$bundleName][] = $this->shortenTemplatePath($file->getRealPath());
            }
        }

        $this->bundleTemplatePathsCache = $result;

        return $result;
    }


    /**
     * Shortens the absolute file path into a relative one by omitting the %kernel.root_dir% part
     *
     * @param $templatePath
     *
     * @return string
     */
    private function shortenTemplatePath ($templatePath)
    {
        // Only shorten the path when the target template path is inside the current Symfony %kernel.root_dir%
        if (strpos($templatePath, $this->appRootDirectory) === 0)
        {
            $templatePath = str_replace($this->appRootDirectory, '.', $templatePath);
        }

        return $templatePath;
    }


    /**
     * Returns a list of all bundle paths where to search the templates
     *
     * @return string[]
     */
    private function getBundlePaths ()
    {
        // If we have a cached version available we use it
        if (!empty($this->bundlePathsCache))
        {
            return $this->bundlePathsCache;
        }

        $result = [];
        // The app itself can contain global templates which also needs to be taken into account
        $result['__global'] = 'app/Resources/views';

        foreach ($this->kernel->getBundles() as $key => $bundle)
        {
            /** @var BundleInterface $bundle */
            $result[$key] = $bundle->getPath();
        }

        $this->bundlePathsCache = $result;

        return $result;
    }
}
