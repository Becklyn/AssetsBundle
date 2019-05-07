<?php

namespace Becklyn\AssetsBundle\Command;

use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class AssetsNamespacesCommand extends Command
{
    public static $defaultName = "becklyn:assets:namespaces";


    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


    /**
     * @var string
     */
    private $projectDir;


    /**
     * @param NamespaceRegistry $namespaceRegistry
     * @param string            $projectDir
     */
    public function __construct (NamespaceRegistry $namespaceRegistry, string $projectDir)
    {
        parent::__construct(self::$defaultName);
        $this->namespaceRegistry = $namespaceRegistry;
        $this->projectDir = $projectDir;
    }


    /**
     * @inheritdoc
     */
    protected function configure ()
    {
        $this
            ->setDescription("Displays an overview of all registered asset namespaces.");
    }


    /**
     * @inheritdoc
     */
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Becklyn Assets: Namespaces");
        $io->comment("Displays all bundle namespaces and their associated paths.\nAll paths are relative to <fg=blue>%kernel.project_dir%</>.");

        $namespaces = $this->fetchNamespaces($this->namespaceRegistry);

        // no namespaces registered -> error and exit
        if (empty($namespaces))
        {
            $io->warning("No asset namespaces registered.");
            return 1;
        }

        // display
        $pathMap = $this->getPathMap($namespaces);
        $hasDuplicatePath = $this->hasDuplicatePath($pathMap);
        $io->table(
            $this->generateTableHeaders($hasDuplicatePath),
            $this->generateTableRows($namespaces, $hasDuplicatePath, $pathMap)
        );

        if ($hasDuplicatePath)
        {
            $io->note("Warning:\nThere are multiple namespaces pointing to the same directory.\nTry to reuse existing namespaces before creating new ones.");
        }

        return 0;
    }


    /**
     * Makes the path relative to the project dir
     *
     * @param string $path
     * @return string
     */
    private function makePathRelative (string $path) : string
    {
        return ($this->projectDir === substr($path, 0, strlen($this->projectDir)))
            ? substr($path, strlen($this->projectDir) + 1)
            : $path;
    }


    /**
     * Fetches and prepares the namespaces
     *
     * @return array
     */
    private function fetchNamespaces (NamespaceRegistry $entryNamespaces) : array
    {
        $namespaces = [];

        foreach ($entryNamespaces as $namespace => $path)
        {
            $namespaces[$namespace] = $this->makePathRelative($path);
        }

        return $namespaces;
    }


    /**
     * Returns the path map.
     * It contains the mapping from relative paths to how often they are registered.
     *
     * @return array
     */
    private function getPathMap (array $namespaces) : array
    {
        $pathMap = [];

        foreach ($namespaces as $namespace => $path)
        {
            $pathMap[$path] = 1 + ($pathMap[$path] ?? 0);
        }

        return $pathMap;
    }


    /**
     * Generates the table rows
     *
     * @param array $namespaces
     * @param bool  $hasDuplicatePath
     * @param array $pathMap
     * @return array
     */
    private function generateTableRows (array $namespaces, bool $hasDuplicatePath, array $pathMap) : array
    {
        $rows = [];

        foreach ($namespaces as $namespace => $path)
        {
            $row = [
                "<fg=yellow>@{$namespace}</>",
                $path,
            ];

            if ($hasDuplicatePath)
            {
                $warning = $pathMap[$path] > 1
                    ? '<fg=red>\\<!></>'
                    : '';

                \array_unshift($row, $warning);
            }


            $rows[$namespace] = $row;
        }

        // sort namespaces alphabetically
        ksort($rows);

        return  $rows;
    }


    /**
     * Generates the table headers
     *
     * @param bool $hasDuplicatePath
     * @return array
     */
    private function generateTableHeaders (bool $hasDuplicatePath) : array
    {
        $headers = ["Namespace", "Relative Path"];

        if ($hasDuplicatePath)
        {
            \array_unshift($headers, "");
        }

        return $headers;
    }


    /**
     * Returns whether the app has a duplicate path
     *
     * @param array $pathMap
     * @return bool
     */
    private function hasDuplicatePath (array $pathMap) : bool
    {
        foreach ($pathMap as $count)
        {
            if (1 < $count)
            {
                return true;
            }
        }

        return false;
    }
}
