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
     *
     * @param string $assetPath
     * @param array  $attributes
     */
    public function __construct (string $assetPath, array $attributes = [])
    {
        $this->assetPath = $assetPath;
        $this->attributes = new HtmlAttributes($attributes);
    }


    //region Accessors
    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     */
    public function setAttribute (string $name, $value) : self
    {
        $this->attributes->set($name, $value);
        return $this;
    }


    /**
     * @return string
     */
    public function getAssetPath () : string
    {
        return $this->assetPath;
    }


    /**
     * @return HtmlAttributes
     */
    public function getAttributes () : HtmlAttributes
    {
        return $this->attributes;
    }


    /**
     * @return bool
     */
    public function isExternal () : bool
    {
        return 0 !== \preg_match('~^(https?:)?//~', $this->assetPath);
    }


    /**
     * @return string|null
     */
    public function getUrl () : ?string
    {
        return $this->url;
    }


    /**
     * @param string|null $url
     */
    public function setUrl (?string $url) : void
    {
        $this->url = $url;
    }
    //endregion
}
