<?php

namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Twig\Extension\Node\AssetsNode;


class AssetReferencesExtractor
{
    /**
     * @var \Twig_Environment
     */
    private $twig;



    /**
     * @param \Twig_Environment $twig
     */
    public function __construct (\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }



    /**
     * @param string $file
     *
     * @return AssetReference[]
     */
    public function extractAssetsFromFile (string $file)
    {
        if (!is_file($file) || !is_readable($file))
        {
            throw new \InvalidArgumentException("File does not exist or is not readable.");
        }

        $tokenStream = $this->twig->tokenize(new \Twig_Source(file_get_contents($file), "assetsExtractor"));
        $syntaxTree = $this->twig->parse($tokenStream);
        return $this->collectAssets($syntaxTree);
    }



    /**
     * Collects all assets in the syntax tree
     *
     * @param \Twig_Node $node
     *
     * @return AssetReference[]
     */
    private function collectAssets (\Twig_Node $node) : array
    {
        $assets = [];

        if ($node instanceof AssetsNode)
        {
            foreach ($node->getAssetReferences() as $reference)
            {
                $assets[] = new AssetReference($reference, $node->getAssetType());
            }
        }

        foreach ($node as $childNode)
        {
            $assets = array_merge(
                $assets,
                $this->collectAssets($childNode)
            );
        }

        return $assets;
    }
}
