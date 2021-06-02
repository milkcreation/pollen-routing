<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class ConflictException extends HttpException
{
    public function __construct(
        string $message = 'Conflict',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(409, $message, $title, $previous, [], $code);
    }
}
