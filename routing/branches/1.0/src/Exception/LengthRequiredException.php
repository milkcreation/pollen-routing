<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class LengthRequiredException extends BaseHttpException
{
    public function __construct(
        string $message = 'Length Required',
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(411, $message, $title, $previous, [], $code);
    }
}
