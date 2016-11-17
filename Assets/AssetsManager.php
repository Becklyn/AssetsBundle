<?php

namespace Becklyn\AssetsBundle\Assets;

use Becklyn\AssetsBundle\Cache\AssetCache;
use Becklyn\AssetsBundle\Finder\TemplateFinder;
use Becklyn\AssetsBundle\Twig\AssetReferencesExtractor;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 *
 */
class AssetsManager
{
    /**
     * @var KernelInterface
     */
    private $kernel;


    /**
     * @var AssetCache
     */
    private $assetCache;


    /**
     * @var TemplateFinder
     */
    private $templateFinder;


    /**
     * @var AssetReferencesExtractor
     */
    private $assetReferencesExtractor;


    /**
     * @var Filesystem
     */
    private $filesystem;



    public function __construct (KernelInterface $kernel, AssetCache $assetCache, TemplateFinder $templateFinder, AssetReferencesExtractor $assetReferencesExtractor)
    {
        $this->kernel = $kernel;
        $this->assetCache = $assetCache;
        $this->templateFinder = $templateFinder;
        $this->assetReferencesExtractor = $assetReferencesExtractor;
        $this->filesystem = new Filesystem();
    }



    /**
     * @param SymfonyStyle $io
     */
    public function regenerateCache (SymfonyStyle $io = null)
    {
        if (null !== $io)
        {
            $io->comment("Removing existing asset files");
        }

        $this->assetCache->clear();

        foreach ($this->kernel->getBundles() as $bundle)
        {
            if (null !== $io)
            {
                $io->section($bundle->getName());
            }

            $bundlePath = $bundle->getPath();
            $templatesInBundle = $this->templateFinder->findInDirectory($bundlePath);

            foreach ($templatesInBundle as $template)
            {
                $assetReferences = $this->assetReferencesExtractor->extractAssetsFromFile($template);

                foreach ($assetReferences as $reference)
                {
                    $this->assetCache->add($reference);
                }

                if (null !== $io)
                {
                    $io->text(sprintf(
                        "Template '%s': %d reference%s found",
                        rtrim($this->filesystem->makePathRelative($template, $bundlePath), "/"),
                        count($assetReferences),
                        1 === count($assetReferences) ? "" : "s"
                    ));
                }
            }
        }
    }
}
