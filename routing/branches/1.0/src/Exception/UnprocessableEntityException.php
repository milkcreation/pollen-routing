<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class UnprocessableEntityException extends HttpException
{
    public function __construct(
        string $message = 'Unprocessable Entity',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(422, $message, $title, $previous, [], $code);
    }
}
