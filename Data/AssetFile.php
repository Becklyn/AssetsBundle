<?php

namespace Becklyn\AssetsBundle\Data;


/**
 * Represents a concrete assets file that is handle via the assets bundle
 */
class AssetFile
{
    const INTEGRITY_HASH_FUNCTION = "sha384";

    /**
     * @var AssetReference
     */
    private $reference;


    /**
     * @var string
     */
    private $filePath;


    /**
     * @var string
     */
    private $contentHash;


    /**
     * @var string
     */
    private $newFileName;



    /**
     * @param AssetReference $reference
     * @param string         $filePath
     * @param string         $contentHash
     * @param string         $newFileName
     */
    public function __construct (AssetReference $reference, string $filePath, string $contentHash, string $newFileName)
    {
        $this->reference = $reference;
        $this->filePath = $filePath;
        $this->contentHash = $contentHash;
        $this->newFileName = $newFileName;
    }



    //region Accessors
    /**
     * @return string
     */
    public function getFilePath () : string
    {
        return $this->filePath;
    }



    /**
     * @return string
     */
    public function getReference () : string
    {
        return $this->reference->getReference();
    }



    /**
     * @return string
     */
    public function getContentHash () : string
    {
        return $this->contentHash;
    }



    /**
     * @return null|string
     */
    public function getHashAlgorithm ()
    {
        return self::INTEGRITY_HASH_FUNCTION;
    }



    /**
     * @return string
     */
    public function getNewFileName () : string
    {
        return "{$this->newFileName}.{$this->reference->getTypeFileExtension()}";
    }
    //endregion
}
