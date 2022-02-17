<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Command\Debug;

use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use Symfony\Component\Console\Style\SymfonyStyle;

class NamespacesPrinter
{
    private NamespaceRegistry $namespaceRegistry;
    private string $projectDir;


    public function __construct (NamespaceRegistry $namespaceRegistry, string $projectDir)
    {
        $this->namespaceRegistry = $namespaceRegistry;
        $this->projectDir = $projectDir;
    }


    /**
     * @return bool whether the namespaces have an issue
     */
    public function printNamespaceInfo (SymfonyStyle $io) : bool
    {
        $io->section("Namespaces");
        $io->comment("Displays all bundle namespaces and their associated paths.");

        $namespaces = $this->fetchNamespaces($this->namespaceRegistry);

        // no namespaces registered -> error and exit
        if (empty($namespaces))
        {
            $io->warning("No asset namespaces registered.");
            return false;
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

        return true;
    }


    /**
     * Generates the table headers.
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
     * Returns whether the app has a duplicate path.
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


    /**
     * Fetches and prepares the namespaces.
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
     * Generates the table rows.
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
        \ksort($rows);

        return  $rows;
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
