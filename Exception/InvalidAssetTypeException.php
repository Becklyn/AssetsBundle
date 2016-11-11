<?php

namespace Becklyn\AssetsBundle\Exception;


use Exception;


class InvalidAssetTypeException extends \InvalidArgumentException
{
    /**
     * @inheritdoc
     */
    public function __construct (string $type, array $allowedTypes, Exception $previous = null)
    {
        $message = sprintf(
            "Unknown type '%s'. Only allowed types are: ",
            $type,
            implode(", ", $allowedTypes)
        );

        parent::__construct($message, 0, $previous);
    }
}
