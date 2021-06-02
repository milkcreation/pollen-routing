<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class UnsupportedMediaException extends HttpException
{
    public function __construct(
        string $message = 'Unsupported Media',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(415, $message, $title, $previous, [], $code);
    }
}
