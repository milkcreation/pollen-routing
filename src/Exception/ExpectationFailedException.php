<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class ExpectationFailedException extends HttpException
{
    public function __construct(
        string $message = 'Expectation Failed',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(417, $message, $title, $previous, [], $code);
    }
}
