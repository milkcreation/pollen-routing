<?php

declare(strict_types=1);

namespace Pollen\Routing\Exception;

use League\Route\Http\Exception\HttpExceptionInterface as BaseHttpExceptionInterface;
use Throwable;

interface HttpExceptionInterface extends BaseHttpExceptionInterface, Throwable
{
    /**
     * Récupération du titre de la page d'affichage de l'exception.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Définition du titre de la page d'affichage de l'exception.
     *
     * @param string $title
     *
     * @return static
     */
    public function setTitle(string $title): HttpExceptionInterface;
}
