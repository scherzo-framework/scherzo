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
use Scherzo\Container;
use Scherzo\HttpException;

class Router
{
    /** HTTP request methods. */
    public const CONNECT = 'CONNECT';
    public const DELETE = 'DELETE';
    public const GET = 'GET';
    public const HEAD = 'HEAD';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const OPTIONS = 'OPTIONS';
    public const PATCH = 'PATCH';
    public const TRACE = 'TRACE';

    protected Dispatcher $dispatcher;

    protected array $options;

    /**
     * The router is created with an array of routes.
     *
     * @param array $routes A list of route configurations.
     * @param array $options Router options e.g. CORS.
     */
    public function __construct(Container $container, array $routes)
    {
        $this->options = $container->safeGet('router:config', []);
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
    public function match(Request $request, Response $response): array|bool
    {
        $method = $request->getMethod();
        $path = $request->getPathInfo();

        $routeInfo = $this->dispatcher->dispatch($method, $path);

        if ($this->options['cors'] ?? false) {
            if (strtolower($request->headers->get('Sec-Fetch-Mode', '')) === 'cors') {
                $response->headers->set('Access-Control-Allow-Origin', $this->options['allowOrigin'] ?? '*');
            }
        }

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                array_shift($routeInfo);
                // [route, params].
                return $routeInfo;

            case Dispatcher::METHOD_NOT_ALLOWED:
                // Handle CORS preflight request.
                if ($method === static::OPTIONS  && $this->options['cors'] ?? false) {
                    $response->headers->set('Access-Control-Allow-Origin', $this->options['allowOrigin'] ?? '*');
                    sort($routeInfo[1]);
                    $response->headers->set('Access-Control-Allow-Methods', implode(', ', $routeInfo[1]));
                    $response->headers->set('Access-Control-Allow-Headers', $this->options['corsHeaders'] ?? '*');
                    return true;
                }

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
