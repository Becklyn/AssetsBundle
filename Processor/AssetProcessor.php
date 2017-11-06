<?php


namespace Becklyn\AssetsBundle\Processor;


interface AssetProcessor
{
    /**
     * Processes the file content
     *
     * @param string $assetPath
     * @param string $fileContent
     * @return string
     */
    public function process (string $assetPath, string $fileContent) : string;
}
