<?php

namespace Becklyn\AssetsBundle\Data;


use Becklyn\AssetsBundle\Exception\InvalidAssetTypeException;


class AssetReference implements DisplayableAssetInterface
{
    const TYPE_JAVASCRIPT = 'js';
    const TYPE_STYLESHEET = 'css';


    /**
     * @var string
     */
    private $reference;


    /**
     * @var string
     */
    private $type;



    /**
     * @param string $reference
     * @param string $type
     */
    public function __construct (string $reference, string $type)
    {
        if (!in_array($type, self::getAllowedTypes(), true))
        {
            throw new InvalidAssetTypeException($type, self::getAllowedTypes());
        }

        $this->reference = $reference;
        $this->type = $type;
    }



    /**
     * @return string
     */
    public function getReference () : string
    {
        return $this->reference;
    }



    /**
     * @return string
     */
    public function getType () : string
    {
        return $this->type;
    }



    /**
     * Returns the file extension for the assets files
     *
     * @return string
     */
    public function getTypeFileExtension () : string
    {
        return $this->getType();
    }



    /**
     * Returns all allowed types
     *
     * @return string[]
     */
    private static function getAllowedTypes () : array
    {
        return [
            self::TYPE_JAVASCRIPT,
            self::TYPE_STYLESHEET,
        ];
    }



    /**
     * @inheritdoc
     */
    public function getRelativeUrl () : string
    {
        return $this->getReference();
    }
}
