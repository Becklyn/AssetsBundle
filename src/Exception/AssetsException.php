<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\Exception;

use Throwable;

class AssetsException extends \Exception
{
    /**
     * @inheritdoc
     */
    public function __construct (string $message = "", Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
