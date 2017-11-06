<?php


namespace Becklyn\AssetsBundle\Processor;


interface AssetProcessor
{
    /**
     * Processes the file content
     *
     * @param string $fileContent
     * @return string
     */
    public function process (string $fileContent) : string;
}
