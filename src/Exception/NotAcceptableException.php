<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class NotAcceptableException extends BaseHttpException
{
    public function __construct(
        string $message = 'Not Acceptable',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(406, $message, $title, $previous, [], $code);
    }
}
