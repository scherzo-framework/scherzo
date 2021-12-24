<?php

/**
 * Match a route to a HTTP request.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2021 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Scherzo\HttpException;

class Router
{
    protected $dispatcher;

    /**
     * The router is created with an array of route.
     *
     * @param array<int, array> $routes A list of route configurations.
     */
    public function __construct(array $routes)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(
            function (RouteCollector $r) use ($routes) {
                foreach ($routes as $route) {
                    $r->addRoute($route[0], $route[1], $route[2]);
                }
            }
        );
    }

    /**
     * Dispatch an HTTP request.
     */
    public function dispatch(string $method, string $path): array
    {
        $routeInfo = $this->dispatcher->dispatch($method, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                array_shift($routeInfo);
                // [route, params].
                return $routeInfo;

            case Dispatcher::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                throw (new HttpException("$method not allowed for $path"))
                    ->setStatusCode(405)
                    ->setAllowedMethods($routeInfo[1]);

            // case Dispatcher::NOT_FOUND:
            default:
                // ... 404 Not Found
                throw (new HttpException("Could not find $path"))
                    ->setStatusCode(404);
        }
    }
}
