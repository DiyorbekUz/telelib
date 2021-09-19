<?php

declare(strict_types=1);

namespace DiyorbekUz\Telelib\Exceptions;

use Exception;
use DiyorbekUz\Telelib\Types\ResponseParameters;
use Throwable;

/**
 * Class ApiError
 *
 * @package DiyorbekUz\Telelib
 * @author Diyorbek 
 */
class ApiError extends Exception
{
    public ?ResponseParameters $parameters = null;

    /**
     * ApiError constructor.
     *
     * @param int $code
     * @param string $message
     * @param ResponseParameters|null $parameters
     * @param Throwable|null $previous
     */
    public function __construct(int $code, string $message, ?ResponseParameters $parameters,
        Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->parameters = $parameters;
    }

    /**
     * @return bool
     */
    public function isMigrated(): bool
    {
        return $this->parameters !== null && $this->parameters->migrate_to_chat_id !== null;
    }

    /**
     * @return bool
     */
    public function isAntiflood(): bool
    {
        return $this->parameters !== null && $this->parameters->retry_after !== null;
    }
}
