<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use Exception;
use League\Route\Http\Exception as BaseHttpException;

class HttpException extends BaseHttpException implements HttpExceptionInterface
{
    /**
     * Titre de la page d'affichage de l'exception.
     * @param string $title
     */
    protected $title = '';

    /**
     * @param int $status
     * @param string|null $message
     * @param string|null $title
     * @param Exception|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct(
        int $status,
        ?string $message = null,
        ?string $title = null,
        ?Exception $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        if ($title !== null) {
            $this->setTitle($title);
        }

        parent::__construct($status, $message, $previous, $headers, $code);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return !empty($this->title) ? $this->title : __CLASS__;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): HttpExceptionInterface
    {
        $this->title = $title;

        return $this;
    }
}