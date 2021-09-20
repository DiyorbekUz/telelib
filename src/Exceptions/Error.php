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
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
