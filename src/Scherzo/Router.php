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
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';

    protected $dispatcher;

    /**
     * The router is created with an array of routes.
     *
     * @param array $routes A list of route configurations.
     */
    public function __construct(array $routes)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(
            function (RouteCollector $r) use ($routes) {
                foreach ($routes as $route) {
                    $r->addRoute(...$route);
                }
            }
        );
    }

    /**
     * Match a route to a method and path or throw an exception.
     */
    public function match(string $method, string $path): array
    {
        $routeInfo = $this->dispatcher->dispatch($method, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                array_shift($routeInfo);
                // [route, params].
                return $routeInfo;

            case Dispatcher::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                throw (new HttpException("Method $method not allowed for path $path"))
                    ->setStatusCode(405)
                    ->setInfo('method', $method)
                    ->setInfo('path', $path)
                    ->setInfo('allowed', $routeInfo[1])
                    ->setAllowedMethods($routeInfo[1]);

            // case Dispatcher::NOT_FOUND:
            default:
                // ... 404 Not Found
                throw (new HttpException("Could not find $path"))
                    ->setInfo('path', $path)
                    ->setStatusCode(404);
        }
    }
}
