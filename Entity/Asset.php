<?php

namespace Becklyn\AssetsBundle\Entity;


class Asset
{
    const TYPE_JAVASCRIPT = 'js';
    const TYPE_STYLESHEET = 'css';


    /**
     * @var string
     */
    private $identifier;


    /**
     * @var string
     */
    private $asset;


    /**
     * @var string
     */
    private $type;


    /**
     * @var string
     */
    private $sourceTemplate;


    /**
     * AssetReference constructor.
     *
     * @param string $identifier
     * @param string $asset
     * @param string $type
     * @param string $sourceTemplate
     */
    public function __construct (string $identifier, string $asset, string $type, string $sourceTemplate)
    {
        $this->asset = $asset;
        $this->identifier = $identifier;
        $this->type = $type;
        $this->sourceTemplate = $sourceTemplate;
    }


    /**
     * @return string
     */
    public function getIdentifier () : string
    {
        return $this->identifier;
    }


    /**
     * @return string
     */
    public function getAsset () : string
    {
        return $this->asset;
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
     * @return string|null
     */
    public function getTypeExtension ()
    {
        switch ($this->type)
        {
            case self::TYPE_JAVASCRIPT:
                return 'js';

            case self::TYPE_STYLESHEET:
                return 'css';

            default:
                return null;
        }
    }


    /**
     * @return string
     */
    public function getSourceTemplate () : string
    {
        return $this->sourceTemplate;
    }


    /**
     * @param string $sourceTemplate
     */
    public function setSourceTemplate ($sourceTemplate)
    {
        $this->sourceTemplate = $sourceTemplate;
    }
}
