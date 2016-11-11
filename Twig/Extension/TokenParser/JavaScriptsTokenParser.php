<?php

namespace Becklyn\AssetsBundle\Twig\Extension\TokenParser;

use Becklyn\AssetsBundle\Twig\Extension\Node\AssetsNode;
use Becklyn\AssetsBundle\Twig\Extension\Node\JavaScriptsNode;


/**
 * Token parser for {% javascripts %} token
 */
class JavaScriptsTokenParser extends AssetsTokenParser
{
    /**
     * @inheritdoc
     */
    protected function createAssetsNode ($files, $body, $lineNo, $tag) : AssetsNode
    {
        return new JavascriptsNode($files, $body, $lineNo, $tag);
    }


    /**
     * @inheritdoc
     */
    public function getTag ()
    {
        return 'javascripts';
    }
}
