<?php

namespace Becklyn\AssetsBundle\File;


class FilePath
{
    /**
     * Resolves an absolute path with a relative one
     *
     * @param string $source    the path to the source
     * @param string $target    the relative path to the target
     * @return string
     */
    public function resolvePath (string $source, string $target) : string
    {
        $sourceDirs = $this->toDirectoryFragments(\dirname($source));
        $targetFileName = \basename($target);

        // if relative path is absolute, just return the absolute path
        if ("/" === \substr($target, 0, 1))
        {
            return $target;
        }

        // strip leading "./"
        if ("./" === \substr($target, 0, 2))
        {
            $target = \substr($target, 2);
        }

        $targetDirs = $this->toDirectoryFragments(\dirname($target));

        // if the relative dirs are empty (it is just a file name), use the same directory
        if (empty($targetDirs))
        {
            return $this->buildFilePath($sourceDirs, $targetFileName);
        }

        // strip leading "../../" from the beginning of the relative dir and simultaneously from the end of the source dir
        while (!empty($targetDirs) && ".." === $targetDirs[0])
        {
            \array_pop($sourceDirs);
            \array_shift($targetDirs);
        }

        return $this->buildFilePath(
            \array_merge($sourceDirs, $targetDirs),
            $targetFileName
        );
    }


    /**
     * Transforms a dir path to path segments
     *
     * @param string $path
     * @return array
     */
    private function toDirectoryFragments (string $path) : array
    {
        $path = \ltrim($path, "/");

        return "." !== $path
            ? \explode("/", $path)
            : [];
    }


    /**
     * Builds a file path from the directory segments with the file name
     *
     * @param array  $directories
     * @param string $fileName
     * @return string
     */
    private function buildFilePath (array $directories, string $fileName) : string
    {
        $dir = !empty($directories)
            ? "/" . implode("/", $directories)
            : "";

        $file = "" !== $fileName
            ? "/{$fileName}"
            : "";

        return $dir . $file;
    }
}
