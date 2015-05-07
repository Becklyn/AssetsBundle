<?php

namespace Becklyn\AssetsBundle\Service;


class AssetConfigurationService
{
    /**
     * @var string
     */
    private $basePath;


    /**
     * @var string
     */
    private $cssDirectory;


    /**
     * @var string
     */
    private $jsDirectory;


    /**
     * @var string
     */
    private $cachePath;


    /**
     * @param string $basePath     The absolute path to the designated cache directory
     * @param string $cssDirectory The relative path to the cache's base path for the CSS directory
     * @param string $jsDirectory  The relative path to the cache's base path for the JS directory
     * @param string $cachePath    The absolute path to the internal cache directory that contains the lookup table
     */
    public function __construct ($basePath, $cssDirectory, $jsDirectory, $cachePath)
    {
        $this->basePath     = $basePath;
        $this->cssDirectory = $cssDirectory;
        $this->jsDirectory  = $jsDirectory;
        $this->cachePath    = $cachePath;
    }


    /**
     * Returns the absolute path to the base cache directory
     *
     * @return string
     */
    public function getLogicalBasePath ()
    {
        return $this->basePath;
    }


    /**
     * Returns the absolute path to the Stylesheets cache directory
     *
     * @return string
     */
    public function getLogicalStylesheetPath ()
    {
        return $this->basePath . '/' . $this->cssDirectory;
    }


    /**
     * Returns the absolute path to the JavaScript cache directory
     *
     * @return string
     */
    public function getLogicalJavascriptPath ()
    {
        return $this->basePath . '/' . $this->jsDirectory;
    }


    /**
     * Returns the relative path of the Stylesheets directory
     *
     * @return string
     */
    public function getRelativeStylesheetDirectory ()
    {
        return $this->cssDirectory;
    }


    /**
     * Returns the relative path of the JavaScripts directory
     *
     * @return string
     */
    public function getRelativeJavascriptDirectory ()
    {
        return $this->jsDirectory;
    }


    /**
     * @return string
     */
    public function getCacheTableFilePath ()
    {
        return $this->cachePath . '/assets_cache_table.php';
    }
}
