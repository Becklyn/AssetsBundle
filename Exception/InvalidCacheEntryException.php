<?php

namespace Becklyn\AssetsBundle\Exception;

use Exception;


class InvalidCacheEntryException extends \RuntimeException
{
    /**
     * @inheritdoc
     */
    public function __construct (string $file, Exception $previous = null)
    {
        $message = sprintf(
            "A cache key for file '%s' does already exists, with a different hash value.",
            $file
        );

        parent::__construct($message, 0, $previous);
    }
}
