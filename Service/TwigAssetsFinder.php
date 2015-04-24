<?php


namespace Becklyn\AssetsBundle\Service;


use Becklyn\AssetsBundle\Entity\AssetCollection;
use Becklyn\AssetsBundle\Entity\AssetReference;
use Becklyn\AssetsBundle\Exception\AssetsBundleBaseException;
use Becklyn\AssetsBundle\Exception\InvalidTwigTemplatePathException;
use Becklyn\AssetsBundle\Exception\TwigTemplateParseException;
use Becklyn\AssetsBundle\Twig\Node\JavaScriptsNode;
use Becklyn\AssetsBundle\Twig\Node\StylesheetsNode;
use Becklyn\AssetsBundle\Twig\Node\CacheableAssetNode;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\TemplateNameParser;
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
     * @var TemplateNameParser
     */
    private $nameParser;


    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;


    /**
     * TwigAssetsFinder constructor.
     *
     * @param Twig_Environment     $twig
     * @param TemplateNameParser   $nameParser
     * @param FileLocatorInterface $fileLocator
     */
    public function __construct (Twig_Environment $twig, TemplateNameParser $nameParser, FileLocatorInterface $fileLocator)
    {
        $this->parser      = $twig->getParser();
        $this->lexer       = $twig->getLexer();
        $this->nameParser  = $nameParser;
        $this->fileLocator = $fileLocator;
    }


    /**
     * Retrieves all assets found in the given Twig templates' nodes:
     * - {% javascripts %}
     * - {% stylesheets %}
     *
     * @param array $bundleTemplatePaths
     *
     * @return array
     */
    public function getAssetPaths (array $bundleTemplatePaths)
    {
        $assets = [];
        $errors = [];

        foreach ($bundleTemplatePaths as $templatePath)
        {
            if (is_null($templatePath))
            {
                continue;
            }

            try
            {
                $assets = array_merge($assets, $this->getTemplateAssets($templatePath));
            }
            catch (AssetsBundleBaseException $e)
            {
                $errors[] = $e->getMessage();
            }
        }

        return [
            'assets' => $assets,
            'errors' => $errors,
        ];
    }


    /**
     * Parses the given Twig template and extracts all assets referenced via the following nodes:
     * - {% stylesheets %}
     * - {% javascripts %}
     *
     * @param string $templatePath
     *
     * @return \Becklyn\AssetsBundle\Entity\AssetCollection[]
     *
     * @throws InvalidTwigTemplatePathException
     * @throws TwigTemplateParseException
     */
    private function getTemplateAssets ($templatePath)
    {
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($templatePath) || strlen($templatePath) === 0)
        {
            throw new InvalidTwigTemplatePathException("The given Twig template couldn't be accessed or does not exist: $templatePath", $templatePath);
        }

        try
        {
            // Load the template contents and create a logical tree which can be parsed
            $templateContent = file_get_contents($templatePath);
            $tokenStream     = $this->lexer->tokenize($templateContent);
            $ast             = $this->parser->parse($tokenStream);

            // Iterate over the tree and extract all assets from all javascripts and stylesheets nodes
            $assetCollections = $this->traverse($templatePath, $ast);

            return $assetCollections;
        }
        catch (IOException $e)
        {
            throw new TwigTemplateParseException("An exception occurred while parsing template $templatePath. Error: " . $e->getMessage(), $templatePath);
        }
        catch (Twig_Error_Syntax $e)
        {
            throw new TwigTemplateParseException("An exception occurred while parsing template $templatePath. Error: " . $e->getMessage(), $templatePath);
        }
    }


    /**
     * Recursively parses the given Twig node and extracts the asset paths if the node
     * is either a JavaScriptNode or StylesheetsNode
     *
     * @param string    $templatePath
     * @param Twig_Node $node
     *
     * @returns AssetCollection[]
     */
    private function traverse ($templatePath, Twig_Node $node)
    {
        $assetCollections = [];

        foreach ($node as $childNode)
        {
            if ($childNode instanceof JavaScriptsNode)
            {
                // Merge all assets from a single tag into an AssetCollection
                $assetCollections[] = new AssetCollection($this->getNodeAssets($childNode), AssetCollection::TYPE_JAVASCRIPT, $templatePath);
            }
            else if ($childNode instanceof StylesheetsNode)
            {
                // Merge all assets from a single tag into an AssetCollection
                $assetCollections[] = new AssetCollection($this->getNodeAssets($childNode), AssetCollection::TYPE_STYLESHEET, $templatePath);
            }

            // Always traverse deeper in the tree to look for nested nodes
            if ($childNode instanceof Twig_Node)
            {
                $assetCollections = array_merge($assetCollections, $this->traverse($templatePath, $childNode));
            }
        }

        return $assetCollections;
    }


    /**
     * Reads the node's attributes and retrieves the asset file paths
     *
     * @param CacheableAssetNode $node
     *
     * @return AssetReference[]
     */
    private function getNodeAssets (CacheableAssetNode $node)
    {
        $assetReferences = [];

        foreach ($node->getAssetReferences() as $templateReference)
        {
            $assetReferences[] = new AssetReference($this->tryResolveRelativeAssetPath($templateReference), $templateReference);
        }

        return $assetReferences;
    }


    /**
     * Resolves any relative bundle asset paths to their absolute counterpart
     * e.g. @AcmeDemoBundle/Resources/public/js/foo.js => /path/to/acme-demo-bundle/Resources/public/js/foo.js
     *
     * Relative non-bundle paths will be ignored
     *
     * @param string $assetReference
     *
     * @return string
     */
    private function tryResolveRelativeAssetPath ($assetReference)
    {
        // Make sure to only expand paths that start with an @
        // e.g. @AcmeDemoBundle/Resources/public/js/foo.js => /path/to/acme-demo-bundle/Resources/public/js/foo.js
        if (strpos($assetReference, '@') === 0)
        {
            try
            {
                $assetReference = $this->fileLocator->locate($this->nameParser->parse($assetReference));
            }
            catch (\RuntimeException $e)
            {
                // Swallow exception silently as this only occurs when the path contains '..' or other illegal chars
            }
            catch (\InvalidArgumentException $e)
            {
                // Swallow exception silently as this only occurs when the path is already absolute
            }
        }

        return $assetReference;
    }
}
