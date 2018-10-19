<?php

namespace Scherzo;

use Scherzo\Pipeline;
use Scherzo\Container;
use Scherzo\HttpService;
use Scherzo\Router;

class Scherzo {

    public function run() {

        $request = isset($request) ? $request : null;

        $container = new Container;

        // Add essential services
        $container->define('http', HttpService::class);
        $container->define('router', Router::class);

        $container->router->addRoutes([
            ['GET', '{path:/.*}', function ($vars, $request) {
                return $this->http->createResponse('Hello Worlds');
            }],
        ]);

        // Build the request pipeline
        $next = new Pipeline($container);

        $next->pushMultiple([
        // Use the HTTP service to send the response.
        ['http', 'sendResponseMiddleware'],

        // Use the HTTP service to parse the request.
        ['http', 'parseRequestMiddleware'],

        // Use the Router service to match a route.
        ['router', 'matchRouteMiddleware'],

        // Use the Router service to dispatch the route.
        ['router', 'dispatchRouteMiddleware'],
        ]);

        // Execute the pipeline
        return $next($next, $request);
    }
}
