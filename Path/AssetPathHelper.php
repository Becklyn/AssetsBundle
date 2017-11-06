<?php

namespace Becklyn\AssetsBundle\Path;


class AssetPathHelper
{
    /**
     * Returns whether the given asset path is a CSS file
     *
     * @param string $path
     * @return bool
     */
    public function isCssFile (string $path)
    {
        return "css" === \pathinfo($path, \PATHINFO_EXTENSION);
    }
}
