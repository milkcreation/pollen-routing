<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;

class ImATeapotException extends BaseHttpException
{
    public function __construct(
        string $message = "I'm a teapot",
        ?string $title = null,
        ?Exception $previous = null,
        int $code = 0
    ) {
        parent::__construct(418, $message, $title, $previous, [], $code);
    }
}
