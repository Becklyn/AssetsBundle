<?php

namespace Becklyn\AssetsBundle\Twig;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Twig\Extension\Node\AssetsNode;
use Psr\Log\LoggerInterface;


class AssetReferencesExtractor
{
    /**
     * @var \Twig_Environment
     */
    private $twig;


    /**
     * @var LoggerInterface
     */
    private $logger;



    /**
     * @param \Twig_Environment    $twig
     * @param LoggerInterface|null $logger
     */
    public function __construct (\Twig_Environment $twig, LoggerInterface $logger = null)
    {
        $this->twig = $twig;
        $this->logger = $logger;
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

        try
        {
            $tokenStream = $this->twig->tokenize(new \Twig_Source(file_get_contents($file), "assetsExtractor"));
            $syntaxTree = $this->twig->parse($tokenStream);

            return $this->collectAssets($syntaxTree);
        }
        catch (\Twig_Error_Syntax $e)
        {
            if (null !== $this->logger)
            {
                $this->logger->warning("Can't parse template '%template%', due to an syntax error: %message%", [
                    "%template%" => $file,
                    "%message%" => $e->getMessage(),
                ]);
            }

            return [];
        }
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
            $assets = $node->getAssetReferences();
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
