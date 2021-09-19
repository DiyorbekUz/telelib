<?php

declare(strict_types=1);

namespace DiyorbekUz\Telelib\Exceptions;

use Throwable;

/**
 * Class Error
 *
 * @package DiyorbekUz\Telelib\Exceptions
 * @author Diyorbek
 */
class Error extends \Error
{
    /**
     * Error constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
