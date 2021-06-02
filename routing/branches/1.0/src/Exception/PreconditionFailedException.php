<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class PreconditionFailedException extends HttpException
{
    public function __construct(
        string $message = 'Precondition Failed',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(412, $message, $title, $previous, [], $code);
    }
}
