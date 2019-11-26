<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Data;

use Becklyn\HtmlBuilder\Node\HtmlAttributes;

class AssetEmbed
{
    //region Fields
    /**
     * @var string
     */
    private $assetPath;


    /**
     * @var HtmlAttributes
     */
    private $attributes;


    /**
     * @var string|null
     */
    private $url;
    //endregion


    /**
     */
    public function __construct (string $assetPath, array $attributes = [])
    {
        $this->assetPath = $assetPath;
        $this->attributes = new HtmlAttributes($attributes);
    }


    //region Accessors
    /**
     * @param mixed $value
     */
    public function setAttribute (string $name, $value) : self
    {
        $this->attributes->set($name, $value);
        return $this;
    }


    /**
     */
    public function getAssetPath () : string
    {
        return $this->assetPath;
    }


    /**
     */
    public function getAttributes () : HtmlAttributes
    {
        return $this->attributes;
    }


    /**
     */
    public function isExternal () : bool
    {
        return 0 !== \preg_match('~^(https?:)?//~', $this->assetPath);
    }


    /**
     */
    public function getUrl () : ?string
    {
        return $this->url;
    }


    /**
     */
    public function setUrl (?string $url) : void
    {
        $this->url = $url;
    }
    //endregion
}
