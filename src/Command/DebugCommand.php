<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Command;

use Becklyn\AssetsBundle\Command\Debug\NamespacesPrinter;
use Becklyn\AssetsBundle\Dependency\DependencyMapFactory;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Finder\AssetsFinder;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugCommand extends Command
{
    public static $defaultName = "becklyn:assets:debug";

    private AssetsFinder $finder;
    private NamespaceRegistry $namespaceRegistry;
    private string $projectDir;
    private NamespacesPrinter $namespacesPrinter;
    private DependencyMapFactory $dependencyMapFactory;


    public function __construct (
        AssetsFinder $finder,
        NamespaceRegistry $namespaceRegistry,
        NamespacesPrinter $namespacesPrinter,
        DependencyMapFactory $dependencyMapFactory,
        string $projectDir
    )
    {
        parent::__construct();
        $this->finder = $finder;
        $this->namespaceRegistry = $namespaceRegistry;
        $this->projectDir = $projectDir;
        $this->namespacesPrinter = $namespacesPrinter;
        $this->dependencyMapFactory = $dependencyMapFactory;
    }


    /**
     * @inheritDoc
     */
    protected function execute (InputInterface $input, OutputInterface $output) : ?int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Debug Assets");

        $io->section("Namespaces Info");
        $success = $this->namespacesPrinter->printNamespaceInfo($io);
        $io->newLine(2);

        $io->section("Dependency Map");

        foreach ($this->dependencyMapFactory->getDependencyMap()->dumpDebugMap() as $file => $dependencies)
        {
            $io->writeln("<fg=blue>{$file}</>");
            $io->listing($dependencies);
        }
        $io->newLine(2);

        $io->section("All Findable Assets");
        $this->printFindableAssets($io);
        return $success ? 0 : 1;
    }



    private function printFindableAssets (SymfonyStyle $io) : void
    {
        $assets = $this->finder->findAssets();
        $rows = [];

        foreach ($assets as $asset)
        {
            try
            {
                $filePath = $this->makePathRelative($this->namespaceRegistry->getFilePath($asset));
            }
            catch (AssetsException $e)
            {
                $filePath = "<fg=red>Error ({$e->getMessage()})</>";
            }

            $rows[$asset->getAssetPath()] = [
                "<fg=yellow>@{$asset->getNamespace()}</>/{$asset->getFilePath()}",
                $filePath,
            ];
        }

        \uksort($rows, "strnatcasecmp");

        $io->table([
            "Asset",
            "Path",
        ], $rows);
    }


    /**
     * Makes the path relative to the project dir.
     */
    private function makePathRelative (string $path) : string
    {
        return ($this->projectDir === \substr($path, 0, \strlen($this->projectDir)))
            ? \substr($path, \strlen($this->projectDir) + 1)
            : $path;
    }
}
