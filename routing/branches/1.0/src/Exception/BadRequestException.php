<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class BadRequestException extends HttpException
{
    public function __construct(
        string $message = 'Bad Request',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(400, $message, $title, $previous, [], $code);
    }
}
