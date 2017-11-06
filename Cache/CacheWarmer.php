<?php

namespace Becklyn\AssetsBundle\Cache;


use Becklyn\AssetsBundle\Asset\AssetsCache;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Finder\AssetsFinder;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;


class CacheWarmer implements CacheWarmerInterface, CacheClearerInterface
{
    /**
     * @var AssetsCache
     */
    private $cache;


    /**
     * @var AssetsFinder
     */
    private $finder;


    /**
     * @param AssetsCache $cache
     */
    public function __construct (AssetsCache $cache, AssetsFinder $finder)
    {
        $this->cache = $cache;
        $this->finder = $finder;
    }





    /**
     * @param SymfonyStyle|null $io
     */
    public function clearCache (?SymfonyStyle $io)
    {
        $this->cache->clear();

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
            $io->text("Searching for all assets in the bundles/ directory...");
        }

        $files = $this->finder->findAssets();

        if (null !== $io)
        {
            $io->text(sprintf(
                "Found <fg=yellow>%d file%s</>.",
                count($files),
                count($files) !== 1 ? "s" : ""
            ));
        }

        if (0 === count($files))
        {
            return;
        }

        if (null !== $io)
        {
            $io->newLine();
            $progressBar = $io->createProgressBar(count($files));
        }

        if (null !== $progressBar)
        {
            $this->cache->addAll($files, [$progressBar, "advance"]);
        }
        else
        {
            $this->cache->addAll($files);
        }

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
