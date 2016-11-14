<?php

namespace Becklyn\AssetsBundle\Data;


/**
 * A reference to a cached asset
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
     * @param string      $relativeUrl
     * @param string|null $contentHash
     */
    public function __construct (string $relativeUrl, string $contentHash = null)
    {
        $this->relativeUrl = $relativeUrl;
        $this->contentHash = $contentHash;
    }



    /**
     * @return string
     */
    public function getRelativeUrl () : string
    {
        return $this->relativeUrl;
    }



    /**
     * @return string|null
     */
    public function getContentHash ()
    {
        return $this->contentHash;
    }
}
