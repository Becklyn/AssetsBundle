<?php


namespace Becklyn\AssetsBundle\Entity;


class AssetCollection implements \IteratorAggregate, \Countable
{
    const TYPE_JAVASCRIPT = 'js';
    const TYPE_STYLESHEET = 'css';


    /**
     * @var string
     */
    private $identifier;


    /**
     * @var AssetReference[]
     */
    private $assets;


    /**
     * @var string
     */
    private $type;


    /**
     * @var string
     */
    private $sourceTemplate;


    /**
     * AssetReference constructor.
     *
     * @param AssetReference[] $assets
     * @param string           $type
     * @param string           $sourceTemplate
     */
    public function __construct (array $assets, $type, $sourceTemplate)
    {
        $this->assets         = $assets;
        $this->identifier     = sha1(implode(':', $assets));
        $this->type           = $type;
        $this->sourceTemplate = $sourceTemplate;
    }


    /**
     * @return string
     */
    public function getIdentifier ()
    {
        return $this->identifier;
    }


    /**
     * @return AssetReference[]
     */
    public function getAssets ()
    {
        return $this->assets;
    }


    /**
     * @return string
     */
    public function getType ()
    {
        return $this->type;
    }


    /**
     * Returns the file extension for the assets files
     *
     * @return string|null
     */
    public function getTypeExtension ()
    {
        switch ($this->type)
        {
            case self::TYPE_JAVASCRIPT:
                return 'js';

            case self::TYPE_STYLESHEET:
                return 'css';

            default:
                return null;
        }
    }


    /**
     * @return string
     */
    public function getSourceTemplate ()
    {
        return $this->sourceTemplate;
    }


    /**
     * @param string $sourceTemplate
     */
    public function setSourceTemplate ($sourceTemplate)
    {
        $this->sourceTemplate = $sourceTemplate;
    }


    // Add some syntactic sugar to support foreach (unfortunately without an automatic type hint for our AssetReference items) and count()

    /**
     * @inheritdoc
     */
    public function getIterator ()
    {
        return new \ArrayIterator($this->assets);
    }


    /**
     * @inheritdoc
     */
    public function count ()
    {
        return iterator_count($this->getIterator());
    }
}
