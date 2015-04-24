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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Templating\TemplateNameParser;
use Twig_Environment;
use Twig_Error_Syntax;
use Twig_Lexer;
use Twig_Node;
use Twig_Parser;

class TwigAssetsFinder
{
    /**
     * @var Kernel
     */
    private $kernel;


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
     * @param Kernel               $kernel
     * @param Twig_Environment     $twig
     * @param TemplateNameParser   $nameParser
     * @param FileLocatorInterface $fileLocator
     */
    public function __construct (Kernel $kernel, Twig_Environment $twig, TemplateNameParser $nameParser, FileLocatorInterface $fileLocator)
    {
        $this->kernel      = $kernel;
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
            // Try resolving the asset reference. If it contains wildcards such as * we may include multiple files at once
            $resolvedAssetPaths = $this->tryResolveRelativeAssetPath($templateReference);

            foreach ($resolvedAssetPaths as $assetPath)
            {
                $assetReferences[] = new AssetReference($assetPath, $templateReference);
            }
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
     * @return string[]
     */
    private function tryResolveRelativeAssetPath ($assetReference)
    {
        $resolvedAssetPaths = [];

        // We only need to manipulate/resolve the asset reference paths if it fulfills any of the following:
        // - contains a bundle reference (e.g. @AcmeDemoBundle/...)
        // - contains a wildcard flag (e.g. ../some/path/**/with/*.wildcards)
        // - all of them above

        // Does the asset reference contain a wildcard?
        if (strpos($assetReference, '*') !== false)
        {
            $result = $this->resolveWildcardPath($assetReference);

            if (!empty($result))
            {
                $resolvedAssetPaths = $result;
            }
        }
        // Does the asset reference contain a bundle reference?
        else if (strpos($assetReference, '@') === 0)
        {
            $result = $this->resolveBundleReferencePath($assetReference);

            if (!is_null($result))
            {
                $resolvedAssetPaths[] = $result;
            }
        }
        // No additional actions required
        else
        {
            $resolvedAssetPaths[] = $assetReference;
        }

        return $resolvedAssetPaths;
    }


    /**
     * Resolves an asset path that includes a bundle reference such as @AcmeDemoBundle/Resources/public/js/foo.js
     *
     * @param string $assetReference
     *
     * @return string|null
     */
    private function resolveBundleReferencePath ($assetReference)
    {
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
     * Resolves wildcard asset references such as:
     * - @AcmeDemoBundle/Resources/public/js/*.js
     * - @AcmeDemoBundle/**\/public/css/*.css
     * - ...
     *
     * to all matching files
     *
     * @param string $assetReference
     *
     * @return string[]
     */
    private function resolveWildcardPath ($assetReference)
    {
        $result = [];

        if (strpos($assetReference, '*') === false)
        {
            return $result;
        }

        $extension = pathinfo($assetReference, PATHINFO_EXTENSION);

        // If no extension is provided it means we could theoretically any asset type, which is not what we want
        if (!$extension)
        {
            return $result;
        }

        // dirname() is smart enough to find the correct directory even for paths like
        // @AcmeDemoBundle/Resources/public/js/*.js and @AcmeDemoBundle/**/public/js/*
        $searchDirectory = dirname($assetReference) . '/';

        // If the asset reference does contain a bundle reference we need to resolve it first
        if (strpos($searchDirectory, '@') === 0)
        {
            // Extract the bundle name reference from the asset path
            // @AcmeDemoBundle/Resources/public/js/*.js ==> @AcmeDemoBundle
            $bundleNameReference = substr($searchDirectory, 0, strpos($searchDirectory, '/'));

            // Let Symfony resolve the asset bundle path
            $bundlePath = $this->kernel->locateResource($bundleNameReference);

            // Now replace the bundle reference with its actual path
            $searchDirectory = str_replace($bundleNameReference . '/', $bundlePath, $searchDirectory);
        }

        // Extract the file name, which could either be a real file name or a wildcard
        $fileName = pathinfo($assetReference, PATHINFO_FILENAME);

        $finder = new Finder();
        $finder
            ->files()
            ->name("$fileName.$extension")
            ->followLinks()
            ->ignoreUnreadableDirs()
            ->in($searchDirectory);

        foreach ($finder as $file)
        {
            /** @var SplFileInfo $file */
            $result[] = $file->getRealPath();
        }

        return $result;
    }
}
