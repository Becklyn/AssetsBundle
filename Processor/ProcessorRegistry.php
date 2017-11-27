<?php

namespace Becklyn\AssetsBundle\Processor;


class ProcessorRegistry
{
    /**
     * @var AssetProcessorInterface[]
     */
    private $processors;


    /**
     *
     * @param AssetProcessorInterface[] $processors
     */
    public function __construct (array $processors)
    {
        $this->processors = $processors;
    }


    /**
     * Returns the asset processor for the given asset path
     *
     * @param string $assetPath
     * @return AssetProcessorInterface|null
     */
    public function get (string $assetPath) : ?AssetProcessorInterface
    {
        $ext = \pathinfo($assetPath, \PATHINFO_EXTENSION);
        return $this->processors[$ext] ?? null;
    }


    /**
     * Returns, whether there is a processor for the given asset path
     *
     * @param string $assetPath
     * @return bool
     */
    public function has (string $assetPath) : bool
    {
        return null !== $this->get($assetPath);
    }
}
