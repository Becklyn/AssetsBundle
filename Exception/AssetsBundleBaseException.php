<?php


namespace Becklyn\AssetsBundle\Exception;


class AssetsBundleBaseException extends \Exception
{
    /**
     * @var string
     */
    private $templatePath;


    /**
     * AssetsBundleBaseException constructor.
     *
     * @param string $message
     * @param string $templatePath
     */
    public function __construct ($message, $templatePath)
    {
        $this->message      = $message;
        $this->templatePath = $templatePath;
    }


    /**
     * @return string
     */
    public function getTemplatePath ()
    {
        return $this->templatePath;
    }
}
