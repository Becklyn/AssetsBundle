<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Command;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Finder\AssetsFinder;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class DebugAssetsCommand extends Command
{
    public static $defaultName = "becklyn:assets:debug";

    /**
     * @var AssetsFinder
     */
    private $finder;


    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


    /**
     * @var Filesystem
     */
    private $filesystem;


    /**
     * @var string
     */
    private $projectDir;


    /**
     *
     * @param AssetsFinder      $finder
     * @param NamespaceRegistry $namespaceRegistry
     * @param Filesystem        $filesystem
     * @param string            $projectDir
     */
    public function __construct (
        AssetsFinder $finder,
        NamespaceRegistry $namespaceRegistry,
        Filesystem $filesystem,
        string $projectDir
    )
    {
        parent::__construct();
        $this->finder = $finder;
        $this->namespaceRegistry = $namespaceRegistry;
        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
    }


    /**
     * @inheritDoc
     */
    protected function execute (InputInterface $input, OutputInterface $output) : ?int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Debug Assets");

        $this->printFindableAssets($io);
        return 0;
    }


    /**
     * @param SymfonyStyle $io
     */
    private function printFindableAssets (SymfonyStyle $io) : void
    {
        $io->section("Findable Assets");
        $assets = $this->finder->findAssets();
        $rows = [];

        foreach ($assets as $asset)
        {
            try {
                $filePath = \rtrim($this->filesystem->makePathRelative(
                    $this->namespaceRegistry->getFilePath($asset),
                    $this->projectDir
                ), "/");

            }
            catch (AssetsException $e)
            {
                $filePath = "?";
            }

            $rows[$asset->getAssetPath()] = [
                "<fg=yellow>@{$asset->getNamespace()}</>/{$asset->getFilePath()}",
                $filePath
            ];
        }

        \uksort($rows, "strnatcasecmp");

        $io->table([
            "Asset",
            "Path"
        ], $rows);
    }
}
