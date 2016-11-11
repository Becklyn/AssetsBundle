<?php

namespace Becklyn\AssetsBundle\Finder;

use Becklyn\AssetsBundle\Entity\Asset;
use Becklyn\AssetsBundle\Twig\Node\JavaScriptsNode;
use Becklyn\AssetsBundle\Twig\Node\StylesheetsNode;
use Becklyn\AssetsBundle\Twig\Node\CacheableAssetNode;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Twig_Environment;
use Twig_Error_Syntax;
use Twig_Lexer;
use Twig_Node;
use Twig_Parser;


class TwigAssetsFinder
{
    /**
     * @var Twig_Lexer
     */
    private $lexer;


    /**
     * @var Twig_Parser
     */
    private $parser;


    /**
     * @var TemplateNameParserInterface
     */
    private $nameParser;


    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;


    /**
     * @var Filesystem
     */
    private $filesystem;


    /**
     * TwigAssetsFinder constructor.
     *
     * @param Twig_Environment            $twig
     * @param TemplateNameParserInterface $nameParser
     * @param FileLocatorInterface        $fileLocator
     * @param Filesystem                  $filesystem
     */
    public function __construct (Twig_Environment $twig, TemplateNameParserInterface $nameParser, FileLocatorInterface $fileLocator, Filesystem $filesystem)
    {
        $this->parser = new Twig_parser($twig);
        $this->lexer = new Twig_Lexer($twig);
        $this->nameParser = $nameParser;
        $this->fileLocator = $fileLocator;
        $this->filesystem = $filesystem;
    }


    /**
     * Retrieves all assets found in the given Twig templates' nodes:
     * - {% javascripts %}
     * - {% stylesheets %}
     *
     * @param array $bundleTemplatePaths
     *
     * @return ArrayCollection[]
     */
    public function getAssetPaths (array $bundleTemplatePaths) : array
    {
        $assets = [];

        foreach ($bundleTemplatePaths as $bundle => $templatePaths)
        {
            foreach ($templatePaths as $templatePath)
            {
                $asset = $this->getTemplateAssets($templatePath);
                if (null === $assets)
                {
                    continue;
                }

                $assets = array_merge($assets, $asset);
            }
        }

        return $assets;
    }


    /**
     * Parses the given Twig template and extracts all assets referenced via the following nodes:
     * - {% stylesheets %}
     * - {% javascripts %}
     *
     * @param string $templatePath
     *
     * @return Asset[]|null
     */
    private function getTemplateAssets ($templatePath)
    {
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($templatePath) || strlen($templatePath) === 0)
        {
            return null;
        }

        try
        {
            // Load the template contents and create a logical tree which can be parsed
            $templateContent = file_get_contents($templatePath);
            $tokenStream = $this->lexer->tokenize($templateContent);
            $ast = $this->parser->parse($tokenStream);

            // Iterate over the tree and extract all assets from all javascripts and stylesheets nodes
            $assets = $this->traverse($templatePath, $ast);

            return $assets;
        }
        catch (IOException $e)
        {
            return null;
        }
        catch (Twig_Error_Syntax $e)
        {
            return null;
        }
    }


    /**
     * Recursively parses the given Twig node and extracts the asset paths if the node
     * is either a JavaScriptNode or StylesheetsNode
     *
     * @param string    $templatePath
     * @param Twig_Node $node
     *
     * @returns Asset[]
     */
    private function traverse ($templatePath, Twig_Node $node) : array
    {
        $assetCollection = [];

        foreach ($node as $childNode)
        {
            if ($childNode instanceof JavaScriptsNode)
            {
                $assets = $this->getNodeAssets($childNode);
                foreach ($assets as $asset)
                {
                    $identifier = $this->generateIdentifier($asset);
                    $assetCollection[] = new Asset($identifier, $asset, Asset::TYPE_JAVASCRIPT, $templatePath);
                }
            }
            else if ($childNode instanceof StylesheetsNode)
            {
                $assets = $this->getNodeAssets($childNode);
                foreach ($assets as $asset)
                {
                    $identifier = $this->generateIdentifier($asset);
                    $assetCollection[] = new Asset($identifier, $asset, Asset::TYPE_STYLESHEET, $templatePath);
                }
            }

            // Always traverse deeper in the tree to look for nested nodes
            if ($childNode instanceof Twig_Node)
            {
                $assetCollection = array_merge($assetCollection, $this->traverse($templatePath, $childNode));
            }
        }

        return $assetCollection;
    }


    /**
     * Reads the node's attributes and retrieves the asset file paths that can be resolved
     *
     * @param CacheableAssetNode $node
     *
     * @return string[]
     */
    private function getNodeAssets (CacheableAssetNode $node) : array
    {
        $assetReferences = [];

        foreach ($node->getAssetReferences() as $templateReference)
        {
            // Try resolving the asset reference. If it contains wildcards such as * we may include multiple files at once
            $resolvedAssetPath = $this->tryResolveAssetPath($templateReference);

            if (null !== $resolvedAssetPath)
            {
                $assetReferences[] = $resolvedAssetPath;
            }

        }

        return $assetReferences;
    }


    /**
     * Resolves any relative bundle asset paths to their absolute counterpart
     * e.g. @AcmeDemoBundle/Resources/public/js/foo.js => /path/to/acme-demo-bundle/Resources/public/js/foo.js
     *
     * For security reasons everything else but a bundle reference path is ignored.
     *
     * @param string $assetReference
     *
     * @return string|null
     */
    private function tryResolveAssetPath ($assetReference)
    {
        // Only allow assets that are referenced via a bundle reference (e.g. @AcmeDemoBundle/...)
        if (strpos($assetReference, '@') !== 0)
        {
            return null;
        }

        try
        {
            // Ask symfony if it can resolve the asset reference
            return $this->fileLocator->locate($this->nameParser->parse($assetReference));
        }
        catch (\RuntimeException $e)
        {
            // Swallow exception silently as this only occurs when the path contains '..' or other illegal chars
        }
        catch (\InvalidArgumentException $e)
        {
            // Swallow exception silently as this only occurs when the path is already absolute
        }

        return null;
    }


    /**
     * Generates an Identifier based on the asset's contents
     *
     * @param string $path
     *
     * @return null|string
     */
    private function generateIdentifier (string $path)
    {
        if (!$this->filesystem->exists($path))
        {
            return null;
        }

        $contents = file_get_contents($path);

        return sha1($contents);
    }
}
