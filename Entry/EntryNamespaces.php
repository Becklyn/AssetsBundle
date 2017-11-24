<?php

namespace Becklyn\AssetsBundle\Entry;


class EntryNamespaces implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $namespaces;


    /**
     * @param string $projectDir
     * @param array  $entries
     */
    public function __construct (string $projectDir, array $entries)
    {
        $this->namespaces = [];

        foreach ($entries as $namespace => $entry)
        {
            $this->namespaces[$namespace] = "{$projectDir}/" . ltrim($entry, "/");
        }
    }


    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator ()
    {
        return new \ArrayIterator($this->namespaces);
    }
}
