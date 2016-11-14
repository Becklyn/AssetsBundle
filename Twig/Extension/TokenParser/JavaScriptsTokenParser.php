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
    protected function createAssetsNode ($files, $lineNo, $tag) : AssetsNode
    {
        return new JavascriptsNode($this->pathGenerator, $files, $lineNo, $tag);
    }


    /**
     * @inheritdoc
     */
    public function getTag ()
    {
        return 'javascripts';
    }
}
