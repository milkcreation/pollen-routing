<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class NotFoundException extends BaseHttpException
{
    public function __construct(
        string $message = 'Not Found',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(404, $message, $title, $previous, [], $code);
    }
}
