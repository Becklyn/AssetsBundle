<?php

namespace Becklyn\AssetsBundle\Cache;

use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Finder\AssetsFinder;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;


class CacheWarmer implements CacheWarmerInterface, CacheClearerInterface
{
    /**
     * @var AssetsRegistry
     */
    private $registry;


    /**
     * @var AssetsFinder
     */
    private $finder;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @param AssetsRegistry $registry
     * @param AssetsFinder   $finder
     * @param bool           $isDebug
     */
    public function __construct (AssetsRegistry $registry, AssetsFinder $finder, bool $isDebug)
    {
        $this->registry = $registry;
        $this->finder = $finder;
        $this->isDebug = $isDebug;
    }


    /**
     * @param SymfonyStyle|null $io
     */
    public function clearCache (?SymfonyStyle $io)
    {
        $this->registry->clear();

        if (null !== $io)
        {
            $io->section("Cache Clear");
            $io->text("<fg=green>Cache cleared.</>");
        }
    }


    /**
     * Warms up the cache
     *
     * @param SymfonyStyle|null $io
     * @throws AssetsException
     */
    public function fillCache (?SymfonyStyle $io)
    {
        $progressBar = null;

        if (null !== $io)
        {
            $io->section("Cache Warm up");
        }

        if ($this->isDebug)
        {
            if (null !== $io)
            {
                $io->comment("Skipping, as the assets are not dumped in debug mode.");
            }

            return;
        }

        if (null !== $io)
        {
            $io->text("Searching for all assets in the bundles/ directory...");
        }

        $assets = $this->finder->findAssets();

        if (null !== $io)
        {
            $io->text(sprintf(
                "Found <fg=yellow>%d asset%s</>.",
                count($assets),
                count($assets) !== 1 ? "s" : ""
            ));
        }

        if (0 === count($assets))
        {
            return;
        }

        if (null !== $io)
        {
            $io->newLine();
            $progressBar = $io->createProgressBar(count($assets));
        }

        $progressCallback = (null !== $progressBar)
            ? [$progressBar, "advance"]
            : null;

        $this->registry->add($assets, $progressCallback);

        if (null !== $io)
        {
            $progressBar->finish();
            $io->newLine(2);
        }
    }


    /**
     * @inheritdoc
     */
    public function isOptional ()
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function warmUp ($cacheDir)
    {
        $this->clearCache(null);
        $this->fillCache(null);
    }


    /**
     * @param string $cacheDir
     */
    public function clear ($cacheDir)
    {
        $this->clearCache(null);
    }
}
