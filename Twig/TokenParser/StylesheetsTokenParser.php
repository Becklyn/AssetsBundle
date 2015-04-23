<?php

namespace Becklyn\AssetsBundle\Twig\TokenParser;

use Becklyn\AssetsBundle\Twig\Node\StylesheetsNode;
use Becklyn\AssetsBundle\Twig\Node\CacheableAssetNode;
use Twig_Node;

class StylesheetsTokenParser extends CacheableAssetTokenParser
{
    /**
     * @param array     $files the file definitions
     * @param Twig_Node $body  the body to compile to
     * @param int       $lineNo
     * @param string    $tag
     *
     * @return CacheableAssetNode
     */
    protected function getCacheNode ($files, $body, $lineNo, $tag)
    {
        return new StylesheetsNode($files, $body, $lineNo, $tag);
    }


    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag ()
    {
        return 'stylesheets';
    }
}
