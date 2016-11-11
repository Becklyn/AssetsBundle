<?php

namespace Becklyn\AssetsBundle\Twig\Extension\Node;

use Becklyn\AssetsBundle\Data\AssetReference;


/**
 * Defines the javascript node
 *
 * @package Becklyn\AssetBundle\TwigNode
 */
class StylesheetsNode extends AssetsNode
{
    /**
     * @inheritdoc
     */
    public function getAssetType () : string
    {
        return AssetReference::TYPE_STYLESHEET;
    }
}
