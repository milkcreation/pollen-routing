<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class TooManyRequestsException extends HttpException
{
    public function __construct(
        string $message = 'Too Many Requests',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(429, $message, $title, $previous, [], $code);
    }
}
