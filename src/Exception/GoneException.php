<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class GoneException extends HttpException
{
    public function __construct(
        string $message = 'Gone',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(410, $message, $title, $previous, [], $code);
    }
}
