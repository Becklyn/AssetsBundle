<?php

namespace Becklyn\AssetsBundle\Data;


/**
 * A cached asset that contains the path and additional important information about the asset
 */
class CachedReference implements DisplayableAssetInterface
{
    /**
     * @var string
     */
    private $relativeUrl;


    /**
     * @var string|null
     */
    private $contentHash;


    /**
     * @var string|null
     */
    private $hashFunction;



    /**
     * @param string $relativeUrl
     * @param string $contentHash
     * @param string $hashFunction
     */
    public function __construct (string $relativeUrl, string $contentHash, string $hashFunction)
    {
        $this->relativeUrl = $relativeUrl;
        $this->contentHash = $contentHash;
        $this->hashFunction = $hashFunction;
    }



    /**
     * @return string
     */
    public function getRelativeUrl () : string
    {
        return $this->relativeUrl;
    }



    /**
     * @return string
     */
    public function getContentHash () : string
    {
        return $this->contentHash;
    }



    /**
     * @return string
     */
    public function getHashFunction () : string
    {
        return $this->hashFunction;
    }
}
