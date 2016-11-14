<?php

namespace Becklyn\AssetsBundle\Twig\Extension\Node;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Data\DisplayableAssetInterface;


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



    /**
     * @inheritdoc
     */
    protected function writeHtmlTag (\Twig_Compiler $compiler, DisplayableAssetInterface $webRelativePath)
    {
        // <link
        $compiler
            ->write('echo \'<link rel="stylesheet"');

        // "href" attribute
        $compiler
            ->raw(' href="\' . $this->env->getExtension("asset")->getAssetUrl(')
            ->repr($webRelativePath->getRelativeUrl())
            ->raw(') . \'"');

        // >
        $compiler
            ->raw('>\';')
            ->raw("\n");
    }
}
