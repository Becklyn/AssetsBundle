<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Helper;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Exception\FileNotFoundException;
use Becklyn\AssetsBundle\File\FileLoader;
use Becklyn\AssetsBundle\Url\AssetUrl;

/**
 * A convenience wrapper around the most commonly used tools of this bundle.
 * An app should probably only ever have to use this class.
 *
 * @api
 */
class AssetHelper
{
    /**
     * @var FileLoader
     */
    private $fileLoader;


    /**
     * @var AssetUrl
     */
    private $assetUrl;


    /**
     */
    public function __construct (FileLoader $fileLoader, AssetUrl $assetUrl)
    {
        $this->fileLoader = $fileLoader;
        $this->assetUrl = $assetUrl;
    }


    /**
     * Returns the file content of the asset untouched.
     *
     * @api
     *
     * @throws AssetsException
     * @throws FileNotFoundException
     */
    public function embed (string $assetPath) : string
    {
        return $this->fileLoader->loadFile(
            Asset::createFromAssetPath($assetPath),
            FileLoader::MODE_UNTOUCHED
        );
    }


    /**
     * Gets the asset URL for the given asset path.
     *
     * @api
     *
     * @throws AssetsException
     */
    public function getUrl (string $assetPath) : string
    {
        return $this->assetUrl->generateUrl(
            Asset::createFromAssetPath($assetPath)
        );
    }
}
