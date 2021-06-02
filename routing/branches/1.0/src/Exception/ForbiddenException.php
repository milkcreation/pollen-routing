<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class ForbiddenException extends HttpException
{
    public function __construct(
        string $message = 'Forbidden',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(403, $message, $title, $previous, [], $code);
    }
}
