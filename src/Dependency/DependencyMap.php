<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Dependency;

use Becklyn\AssetsBundle\Data\AssetEmbed;

class DependencyMap
{
    /**
     * @var array
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
        $toLoad = [];

        foreach ($imports as $import)
        {
            $dirname = \dirname($import);
            $basename = \basename($import);

            if ("js" === \pathinfo($basename, \PATHINFO_EXTENSION))
            {
                $legacy = "{$dirname}/_legacy.{$basename}";

                if (\array_key_exists($import, $this->map) && \array_key_exists($legacy, $this->map))
                {
                    $toLoad = $this->loadForSingleImport($toLoad, $import, ["type" => "module"]);
                    $toLoad = $this->loadForSingleImport($toLoad, $legacy, ["nomodule" => true]);
                }

                continue;
            }

            // load normally
            $toLoad = $this->loadForSingleImport($toLoad, $import);
        }

        return $toLoad;
    }


    /**
     * @param string $import
     *
     * @return AssetEmbed[]
     */
    private function loadForSingleImport (array $allImports, string $import, array $embedAttributes = []) : array
    {
        $dependencies = $this->map[$import] ?? null;

        // add dependencies before the script itself
        if (null !== $dependencies)
        {
            foreach ($dependencies as $dependency)
            {
                $allImports[$dependency] = new AssetEmbed($dependency, $embedAttributes);
            }
        }
        else
        {
            $allImports[$import] = new AssetEmbed($import, $embedAttributes);
        }

        return $allImports;
    }
}
