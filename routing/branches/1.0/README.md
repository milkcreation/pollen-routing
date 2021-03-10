# Pollen Routing Component

[![Latest Version](https://img.shields.io/badge/release-1.0.0-blue?style=for-the-badge)](https://www.presstify.com/pollen-solutions/routing/)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE.md)
[![PHP Supported Versions](https://img.shields.io/badge/PHP->=7.4-8892BF?style=for-the-badge&logo=php)](https://www.php.net/supported-versions.php)

Pollen **Routing** Component provide a layer of HTTP request mapping and HTTP response resolution.

## Installation

```bash
composer require pollen-solutions/routing
```

## Basic Usage

```php
use Pollen\Http\Request;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Routing\Router;

// Router instantiation
$router = new Router();

// Map a route
$router->map('GET', '/', function (): ResponseInterface {
return new Response('<h1>Hello, World!</h1>');
});

// Setting Handle Request (optionnal)
$request = Request::createFromGlobals();
$router->setHandleRequest($request);

// Map a Fallback Route (optionnal)
$router->setFallback(function () {
    return new Response('<h1>404</h1>', 404);
});

// Catch HTTP Response
$response = $router->handleRequest();

// Send the response to the browser
$router->sendResponse($response);

// Trigger the terminate event
$router->terminateEvent($request, $response);
```
