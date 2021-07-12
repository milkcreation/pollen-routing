<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use League\Route\Http\Exception\HttpExceptionInterface as BaseHttpExceptionInterface;
use Throwable;

interface HttpExceptionInterface extends BaseHttpExceptionInterface, Throwable
{
    /**
     * Get HTML page title showing the exception.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Set HTML page title showing the exception.
     *
     * @param string $title
     *
     * @return static
     */
    public function setTitle(string $title): HttpExceptionInterface;
}
