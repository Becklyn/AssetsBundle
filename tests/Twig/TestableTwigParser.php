<?php

namespace Becklyn\AssetsBundle\tests\Twig;

use Twig_Environment;
use Twig_ExpressionParser;
use Twig_Parser;
use Twig_TokenStream;


class TestableTwigParser extends Twig_Parser
{
    /**
     * @inheritdoc
     *
     * This constructor is exclusively so the MockBuilder can pass arguments to it
     * since apparently it's incapable of passing args to parent constructors.
     */
    public function __construct (\Twig_Environment $twig)
    {
        parent::__construct($twig);
    }


    /**
     * @param Twig_Environment $env
     */
    public function setTwigEnvironment (Twig_Environment $env)
    {
        $this->env = $env;
    }


    /**
     * @param Twig_ExpressionParser $expressionParser
     */
    public function setExpressionParser (Twig_ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }


    /**
     * @param Twig_TokenStream $stream
     */
    public function setStream (Twig_TokenStream $stream)
    {
        $this->stream = $stream;
    }
}
