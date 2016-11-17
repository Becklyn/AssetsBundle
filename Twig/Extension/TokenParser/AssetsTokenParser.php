<?php

namespace Becklyn\AssetsBundle\Twig\Extension\TokenParser;

use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\Twig\Extension\Node\AssetsNode;
use Twig_Token;
use Twig_TokenParser;


/**
 * Base token parser for the assets tokens defined by this bundle
 */
abstract class AssetsTokenParser extends Twig_TokenParser
{
    /**
     * @var PathGenerator
     */
    protected $pathGenerator;



    /**
     * @param PathGenerator $pathGenerator
     */
    public function __construct (PathGenerator $pathGenerator)
    {
        $this->pathGenerator = $pathGenerator;
    }

    /**
     * @inheritdoc
     */
    public function parse (Twig_Token $token)
    {
        $stream = $this->parser->getStream();


        $files = [];

        // parse all parameters
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE))
        {
            $files[] = $this->parser->getExpressionParser()->parseExpression();
        }

        if (empty($files))
        {
            throw new \Twig_Error_Syntax(
                sprintf("No files were specified in the '%s' block.", $this->getTag()),
                $stream->getCurrent()->getLine(),
                $stream->getSourceContext()->getName()
            );
        }

        // %}
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        // return created node
        return $this->createAssetsNode($files, $token->getLine(), $this->getTag());
    }


    /**
     * Creates the asset node
     *
     * @param array  $files the file definitions
     * @param int    $lineNo
     * @param string $tag
     *
     * @return AssetsNode
     */
    protected abstract function createAssetsNode ($files, $lineNo, $tag) : AssetsNode;


    /**
     * @inheritdoc
     */
    abstract public function getTag ();
}
