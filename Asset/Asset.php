<?php

namespace Becklyn\AssetsBundle\Asset;


class Asset
{
    /**
     * @var string
     */
    private $outputDirectory;


    /**
     * @var string
     */
    private $digest;


    /**
     * @var string
     */
    private $outputFileName;


    /**
     * @param string $filePath
     * @param string $hash
     */
    public function __construct (string $outputDirectory, string $filePath, string $hash)
    {
        $this->outputDirectory = $outputDirectory;
        $this->digest = $hash;
        $this->outputFileName = $this->generateOutputFileName($filePath, $hash);
    }


    /**
     * Generates the output filename
     *
     * @param string $filePath
     * @param string $hash
     * @return string
     */
    private function generateOutputFileName (string $filePath, string $hash) : string
    {
        $extension = \pathinfo($filePath, \PATHINFO_EXTENSION);

        $sanitizedHash = \strtr($hash, [
            "+" => "_",
            "/" => "-",
            "=" => "",
        ]);

        return \basename($filePath, $extension) . substr($sanitizedHash, 0, 20) . ".{$extension}";
    }


    /**
     * @return string
     */
    public function getOutputFilePath () : string
    {
        return "{$this->outputDirectory}/{$this->outputFileName}";
    }


    /**
     * @return string
     */
    public function getDigest ()
    {
        return $this->digest;
    }
}
