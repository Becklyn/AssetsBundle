<?php

namespace Becklyn\AssetsBundle\Command;


use Becklyn\AssetsBundle\Cache\CacheWarmer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class AssetsCacheCommand extends Command
{
    /**
     * @var CacheWarmer
     */
    private $cacheWarmer;


    /**
     * @param CacheWarmer $cacheWarmer
     */
    public function __construct (CacheWarmer $cacheWarmer)
    {
        $this->cacheWarmer = $cacheWarmer;
        parent::__construct();
    }


    /**
     * @inheritdoc
     */
    protected function configure ()
    {
        $this
            ->setDescription("Resets (clears and warms) the assets cache")
            ->addOption(
                "no-warmup",
                null,
                InputOption::VALUE_OPTIONAL,
                "Should be cache be automatically warmed up?",
                false
            );
    }


    /**
     * @inheritdoc
     */
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Becklyn Assets: Reset");
        $this->cacheWarmer->clearCache($io);

        if (!$input->getOption("no-warmup"))
        {
            $this->cacheWarmer->fillCache($io);
        }

        $io->success("finished");
    }
}
