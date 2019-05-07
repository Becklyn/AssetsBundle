<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Command;

use Becklyn\AssetsBundle\Cache\CacheWarmer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AssetsInstallCommand extends Command
{
    public static $defaultName = "becklyn:assets:install";


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
        parent::__construct(self::$defaultName);
    }


    /**
     * @inheritdoc
     */
    protected function configure () : void
    {
        $this
            ->setDescription("Installs the assets. Clears the dump directory, re-adds all files and fills the cache.")
            ->addOption(
                "no-warmup",
                null,
                InputOption::VALUE_OPTIONAL,
                "Should the cache be automatically warmed up?",
                false
            );
    }


    /**
     * @inheritdoc
     */
    protected function execute (InputInterface $input, OutputInterface $output) : ?int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Becklyn Assets: Install");
        $this->cacheWarmer->clearCache($io);

        if (!$input->getOption("no-warmup"))
        {
            $this->cacheWarmer->fillCache($io);
        }

        $io->success("finished");
        return 0;
    }
}
