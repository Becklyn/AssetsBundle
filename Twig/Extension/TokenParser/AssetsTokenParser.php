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

        // %}
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        // return created node
        return $this->createAssetsNode($files, $token->getLine(), $this->getTag());
    }


    /**
     * Determines whether or not the node's body contents needs to be parsed
     *
     * @param Twig_Token $token
     *
     * @return bool
     */
    public function decideIfSubparse (Twig_Token $token)
    {
        return $token->test([$this->getEndTag()]);
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
