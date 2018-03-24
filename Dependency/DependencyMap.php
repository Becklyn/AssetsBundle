<?php

namespace Becklyn\AssetsBundle\Dependency;


class DependencyMap
{
    /**
     * @var array
     */
    private $map = [];


    /**
     * Constructs the dependency map
     *
     * Please note that every list of dependencies MUST include the file itself
     *
     * @param array $dependencyMap
     */
    public function __construct (array $dependencyMap)
    {
        $this->map = $dependencyMap;
    }


    /**
     * Returns the imports with all dependencies
     *
     * @param array $imports
     * @return array
     */
    public function getImportsWithDependencies (array $imports)
    {
        $toLoad = [];

        foreach ($imports as $import)
        {
            $dependencies = $this->map[$import] ?? null;

            if (null !== $dependencies)
            {
                foreach ($dependencies as $dependency)
                {
                    $toLoad[$dependency] = true;
                }
            }
            else
            {
                $toLoad[$import] = true;
            }
        }

        return \array_keys($toLoad);
    }
}
