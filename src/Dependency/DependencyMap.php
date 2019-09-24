<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Dependency;

use Becklyn\AssetsBundle\Data\AssetEmbed;
use Becklyn\AssetsBundle\Dependency\Dependency\AssetDependency;

class DependencyMap
{
    private const NEUTRAL = 0;
    private const MODERN = 1;
    private const LEGACY = 2;

    /**
     * @var string[][]
     */
    private $map = [];


    /**
     * Constructs the dependency map.
     *
     * Please note that every list of dependencies MUST include the file itself
     *
     * @param array $dependencyMap
     */
    public function __construct (array $dependencyMap = [])
    {
        $this->map = $dependencyMap;
    }


    /**
     * Returns the imports with all dependencies.
     *
     * @param array $imports
     *
     * @return AssetEmbed[]
     */
    public function getImportsWithDependencies (array $imports) : array
    {
        /** @var AssetDependency[] $toLoad */
        $toLoad = [];

        foreach ($imports as $import)
        {
            $dirname = \dirname($import);
            $basename = \basename($import);

            $dirname = ("." === $dirname)
                ? ""
                : "{$dirname}/";

            if ("js" === \pathinfo($basename, \PATHINFO_EXTENSION))
            {
                $legacy = "{$dirname}_legacy.{$basename}";
                $modern = "{$dirname}_modern.{$basename}";

                // load legacy Kaba builds: `entry` + `_legacy.entry`
                if (\array_key_exists($import, $this->map) && \array_key_exists($legacy, $this->map))
                {
                    $toLoad = $this->loadForSingleImport($toLoad, $import, self::MODERN);
                    $toLoad = $this->loadForSingleImport($toLoad, $legacy, self::LEGACY);
                    continue;
                }

                // load modern Kaba builds: `entry` + `_modern.entry`
                if (\array_key_exists($import, $this->map) && \array_key_exists($modern, $this->map))
                {
                    $toLoad = $this->loadForSingleImport($toLoad, $import, self::LEGACY);
                    $toLoad = $this->loadForSingleImport($toLoad, $modern, self::MODERN);
                    continue;
                }
            }

            // load normally
            $toLoad = $this->loadForSingleImport($toLoad, $import, self::NEUTRAL);
        }

        return \array_map(
            function (AssetDependency $dependency)
            {
                return $dependency->getAssetEmbed();
            },
            $toLoad
        );
    }


    /**
     * @param AssetDependency[] $allImports
     * @param string            $import
     * @param int               $modernOrLegacy
     *
     * @return AssetDependency[]
     */
    private function loadForSingleImport (array $allImports, string $import, int $modernOrLegacy) : array
    {
        $dependencies = $this->map[$import] ?? null;

        // add dependencies before the script itself
        if (null !== $dependencies)
        {
            foreach ($dependencies as $dependency)
            {
                $allImports = $this->createDependency($dependency, $allImports, $modernOrLegacy);
            }
        }
        else
        {
            $allImports = $this->createDependency($import, $allImports, $modernOrLegacy);
        }

        return $allImports;
    }


    /**
     * @param string            $name
     * @param AssetDependency[] $map
     * @param int               $modernOrLegacy
     *
     * @return AssetDependency[]
     */
    private function createDependency (string $name, array $map, int $modernOrLegacy) : array
    {
        if (!\array_key_exists($name, $map))
        {
            $map[$name] = new AssetDependency($name);
        }

        $dependency = $map[$name];

        if (self::MODERN === $modernOrLegacy)
        {
            $dependency->setModern();
        }
        elseif (self::LEGACY === $modernOrLegacy)
        {
            $dependency->setLegacy();
        }

        return $map;
    }
}
