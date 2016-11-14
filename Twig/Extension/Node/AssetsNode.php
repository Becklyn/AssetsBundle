<?php

namespace Becklyn\AssetsBundle\Twig\Extension\Node;

use Becklyn\AssetsBundle\Cache\AssetCache;
use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Path\PathGenerator;
use Twig_Compiler;
use Twig_Node;
use Twig_Node_Expression_Binary_Concat;
use Twig_Node_Expression_Constant;
use Twig_Node_Spaceless;


abstract class AssetsNode extends Twig_Node
{
    /**
     * @var PathGenerator
     */
    private $pathGenerator;



    /**
     * @param PathGenerator $pathGenerator
     * @param string[]      $files the file definitions
     * @param int           $lineNo
     * @param string        $tag
     */
    public function __construct (PathGenerator $pathGenerator, array $files, int $lineNo, string $tag)
    {
        $this->pathGenerator = $pathGenerator;

        parent::__construct(
            [
                'files' => new Twig_Node($files),
            ],
            [],
            $lineNo,
            $tag
        );
    }



    /**
     * @param string $webRelativePath
     *
     * @return mixed
     */
    abstract protected function writeHtmlTag (string $webRelativePath);


    /**
     * {@inheritdoc}
     */
    public function compile (Twig_Compiler $compiler)
    {
        // Add debug info which will generate some comments in the translated Twig file which maps the template's line number to the code's line number
        $compiler
            ->addDebugInfo($this);

        $assetReferences = $this->getAssetReferences();

        // if there are no files given - just compile to nothing
        if (empty($assetReferences))
        {
            return;
        }

        // dump all references
        foreach ($assetReferences as $reference)
        {
            $webRelativePath = $this->pathGenerator->getRelativeUrl($reference);

            $compiler
                ->write('echo ')
                ->repr($this->writeHtmlTag($webRelativePath))
                ->write(";\n");
        }
    }


    /**
     * Parses the 'files' attribute and returns all values as string
     *
     * @return AssetReference[]
     */
    public function getAssetReferences () : array
    {
        $result = [];

        foreach ($this->getNode('files') as $file)
        {
            $result[] = new AssetReference($this->getNodePath($file), $this->getAssetType());
        }

        return $result;
    }


    /**
     * Extracts the path for the given Twig Node and tries to resolve the asset path
     *
     * @param Twig_Node $node
     *
     * @return array
     */
    private function getNodePath (Twig_Node $node)
    {
        $assetPath = null;

        if ($node instanceof Twig_Node_Expression_Constant)
        {
            $assetPath = $node->getAttribute('value');
        }
        else if ($node instanceof Twig_Node_Expression_Binary_Concat)
        {
            $assetPath =  $this->getNodePath($node->getNode('left')) . $this->getNodePath($node->getNode('right'));
        }

        return $assetPath;
    }



    /**
     * Returns the asset type
     *
     * @return string
     */
    abstract public function getAssetType () : string;
}
