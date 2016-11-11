<?php

namespace Becklyn\AssetsBundle\Twig\Extension\TokenParser;

use Becklyn\AssetsBundle\Twig\Extension\Node\AssetsNode;
use Twig_Node;
use Twig_Token;
use Twig_TokenParser;


/**
 * Base token parser for the assets tokens defined by this bundle
 */
abstract class AssetsTokenParser extends Twig_TokenParser
{
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

        // parse body between %} ... {%
        $body = $this->parser->subparse([$this, 'decideIfSubparse']);

        // expect end tag
        $stream->expect(Twig_Token::NAME_TYPE, $this->getEndTag());

        // %}
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        // return created node
        return $this->createAssetsNode($files, $body, $token->getLine(), $this->getTag());
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
     * @param array     $files the file definitions
     * @param Twig_Node $body  the body to compile to
     * @param int       $lineNo
     * @param string    $tag
     *
     * @return AssetsNode
     */
    protected abstract function createAssetsNode ($files, $body, $lineNo, $tag) : AssetsNode;



    /**
     * Returns the closing tag of this node
     *
     * @return string
     */
    public function getEndTag () : string
    {
        return "end{$this->getTag()}";
    }


    /**
     * @inheritdoc
     */
    abstract public function getTag ();
}
