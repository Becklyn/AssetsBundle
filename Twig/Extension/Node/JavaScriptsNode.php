<?php

namespace Becklyn\AssetsBundle\Twig\Extension\Node;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\CachedReference;
use Becklyn\AssetsBundle\Data\DisplayableAssetInterface;


/**
 * Defines the javascript node
 *
 * @package Becklyn\AssetBundle\TwigNode
 */
class JavaScriptsNode extends AssetsNode
{
    /**
     * @inheritdoc
     */
    public function getAssetType () : string
    {
        return AssetReference::TYPE_JAVASCRIPT;
    }


    /**
     * @inheritdoc
     */
    protected function writeHtmlTag (\Twig_Compiler $compiler, DisplayableAssetInterface $webRelativePath)
    {
        // <script
        $compiler
            ->write('echo \'<script');

        // "src" attribute
        $compiler
            ->raw(' src="\' . $this->env->getExtension("asset")->getAssetUrl(')
            ->repr($webRelativePath->getRelativeUrl())
            ->raw(') . \'"');

        if ($webRelativePath instanceof CachedReference)
        {
            // "integrity" attribute
            $compiler
                ->raw(' integrity="' . $webRelativePath->getHashFunction() . '-' . $webRelativePath->getContentHash() . '"');
        }

        // ></script>
        $compiler
            ->raw('></script>\';')
            ->raw("\n");
    }
}
