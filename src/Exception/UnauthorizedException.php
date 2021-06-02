<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class UnauthorizedException extends HttpException
{
    public function __construct(
        string $message = 'Unauthorized',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(401, $message, $title, $previous, [], $code);
    }
}
