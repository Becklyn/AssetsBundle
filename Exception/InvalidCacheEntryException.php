<?php

namespace Becklyn\AssetsBundle\Exception;

use Becklyn\AssetsBundle\Data\AssetReference;
use Exception;


class InvalidCacheEntryException extends \RuntimeException
{
    /**
     * @inheritdoc
     */
    public function __construct (AssetReference $reference, Exception $previous = null)
    {
        $message = sprintf(
            "A cache key for file '%s' does already exists, with a different hash value.",
            $reference->getReference()
        );

        parent::__construct($message, 0, $previous);
    }
}
