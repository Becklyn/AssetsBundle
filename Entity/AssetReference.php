<?php


namespace Becklyn\AssetsBundle\Entity;


class AssetReference
{
    /**
     * @var string
     */
    private $filePath;


    /**
     * @var string
     */
    private $templateReference;


    /**
     * AssetFile constructor.
     *
     * @param string $filePath
     * @param string $templateReference
     */
    public function __construct ($filePath, $templateReference)
    {
        $this->filePath          = $filePath;
        $this->templateReference = $templateReference;
    }


    /**
     * @return string
     */
    public function getFilePath ()
    {
        return $this->filePath;
    }


    /**
     * @return string
     */
    public function getTemplateReference ()
    {
        return $this->templateReference;
    }


    /**
     * @inheritdoc
     */
    function __toString ()
    {
        return $this->templateReference;
    }
}
