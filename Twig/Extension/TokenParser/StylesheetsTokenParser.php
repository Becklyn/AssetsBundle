<?php

namespace Becklyn\AssetsBundle\Twig\Extension\TokenParser;

use Becklyn\AssetsBundle\Twig\Extension\Node\AssetsNode;
use Becklyn\AssetsBundle\Twig\Extension\Node\StylesheetsNode;


/**
 * Token parser for {% stylesheets %} token
 */
class StylesheetsTokenParser extends AssetsTokenParser
{
    /**
     * @inheritdoc
     */
    protected function createAssetsNode ($files, $lineNo, $tag) : AssetsNode
    {
        return new StylesheetsNode($files, $lineNo, $tag);
    }


    /**
     * @inheritdoc
     */
    public function getTag ()
    {
        return 'stylesheets';
    }
}
