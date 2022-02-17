<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Dependency\Dependency;

use Becklyn\AssetsBundle\Data\AssetEmbed;

class AssetDependency
{
    private string $name;
    private bool $modern = false;
    private bool $legacy = false;


    public function __construct (string $name)
    {
        $this->name = $name;
    }


    public function setModern () : void
    {
        $this->modern = true;
    }


    public function setLegacy () : void
    {
        $this->legacy = true;
    }


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
