<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class MethodNotAllowedException extends HttpException
{
    public function __construct(
        array $allowed = [],
        string $message = 'Method Not Allowed',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        $headers = [
            'Allow' => implode(', ', $allowed),
        ];

        parent::__construct(405, $message, $title, $previous, $headers, $code);
    }
}
