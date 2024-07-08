<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

use Exception;
use Throwable;

class OpenWebNetException extends Exception
{
    public const CODE_CANNOT_CONNECT = 1;
    public const CODE_WRONG_REPLY = 2;
    public const CODE_AUTHENTICATION_ERROR = 3;
    public const CODE_NO_REPLY = 4;
    public const CODE_TIME_NOT_SUPPORTED = 5;
    public const CODE_DIMMER_LEVEL_NOT_SUPPORTED = 6;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        OpenWebNetDebugging::logTime("Exception thrown: [$code] - $message", OpenWebNetDebuggingLevel::NORMAL);
    }
}
