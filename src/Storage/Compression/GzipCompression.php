<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Storage\Compression;

use Symfony\Component\Process\Process;

class GzipCompression
{
    /**
     * @var bool
     */
    private $initialized = false;


    /**
     * @var array|null
     */
    private $compressor;


    /**
     */
    private function getCompressor () : ?array
    {
        if (!$this->initialized)
        {
            $this->initialized = true;
            $this->compressor = $this->discoverCompressor();
        }

        return $this->compressor;
    }


    /**
     */
    private function discoverCompressor () : ?array
    {
        // Mapping of command names to their required parameters
        $availableCommands = [
            "zopfli" => [],
            "gzip" => ["--best", "--keep"],
        ];

        foreach ($availableCommands as $name => $arguments)
        {
            $process = new Process(["command", "-v", $name]);
            $process->setTimeout(10);
            $process->run();
            $output = \trim($process->getOutput());

            if ($process->isSuccessful() && "" !== $output)
            {
                \array_unshift($arguments, $name);
                return $arguments;
            }
        }

        return null;
    }


    /**
     * Tries to compress the given file and writes the output to the same path with added ".gz".
     *
     * @return bool whether the file was compressed
     */
    public function compressFile (string $filePath) : bool
    {
        $compressor = $this->getCompressor();

        if (null === $compressor)
        {
            return false;
        }

        $process = new Process(\array_merge($compressor, [$filePath]));
        $process->run();
        return $process->isSuccessful();
    }
}
