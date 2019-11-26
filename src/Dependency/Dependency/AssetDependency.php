<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Dependency\Dependency;

use Becklyn\AssetsBundle\Data\AssetEmbed;

class AssetDependency
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $modern = false;

    /**
     * @var bool
     */
    private $legacy = false;


    /**
     */
    public function __construct (string $name)
    {
        $this->name = $name;
    }


    /**
     *
     */
    public function setModern () : void
    {
        $this->modern = true;
    }


    /**
     *
     */
    public function setLegacy () : void
    {
        $this->legacy = true;
    }


    /**
     */
    public function getAssetEmbed () : AssetEmbed
    {
        $attributes = [];

        if ($this->modern && !$this->legacy)
        {
            $attributes = ["type" => "module"];
        }
        elseif (!$this->modern && $this->legacy)
        {
            $attributes = ["nomodule" => true];
        }

        return new AssetEmbed($this->name, $attributes);
    }
}
