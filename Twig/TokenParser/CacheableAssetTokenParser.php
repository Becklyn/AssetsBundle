<?php

namespace Becklyn\AssetsBundle\Twig\TokenParser;

use Becklyn\AssetsBundle\Cache\AssetCache;
use Becklyn\AssetsBundle\Twig\Node\CacheableAssetNode;
use Twig_Error_Syntax;
use Twig_Node;
use Twig_Token;
use Twig_TokenParser;


abstract class CacheableAssetTokenParser extends Twig_TokenParser
{
    /**
     * @var AssetCache
     */
    protected $assetCache;


    /**
     * TwigCacheableAssetTokenParser constructor.
     *
     * @param AssetCache $assetCache
     */
    public function __construct (AssetCache $assetCache)
    {
        $this->assetCache = $assetCache;
    }


    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_Node A Twig_Node instance
     *
     * @throws Twig_Error_Syntax
     */
    public function parse (Twig_Token $token)
    {
        $stream = $this->parser->getStream();

        $files = [];
        // parse all file definitions
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE))
        {
            $files[] = $this->parser->getExpressionParser()->parseExpression();
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse([$this, 'decideIfSubparse']);

        $stream->expect(Twig_Token::NAME_TYPE, $this->getEndTag());
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $node = $this->getCacheNode($files, $body, $token->getLine(), $this->getTag());
        $node->setAssetCache($this->assetCache);

        return $node;
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
     * @param array     $files the file definitions
     * @param Twig_Node $body  the body to compile to
     * @param int       $lineNo
     * @param string    $tag
     *
     * @return CacheableAssetNode
     */
    protected abstract function getCacheNode ($files, $body, $lineNo, $tag);


    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag ()
    {
        return 'assetnode';
    }


    /**
     * Gets the tag end name associated with this token parser.
     *
     * @return string The tag end name
     */
    public function getEndTag ()
    {
        return 'end' . $this->getTag();
    }
}
