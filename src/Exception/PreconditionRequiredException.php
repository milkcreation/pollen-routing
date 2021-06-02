<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class PreconditionRequiredException extends HttpException
{
    public function __construct(
        string $message = 'Precondition Required',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(428, $message, $title, $previous, [], $code);
    }
}
