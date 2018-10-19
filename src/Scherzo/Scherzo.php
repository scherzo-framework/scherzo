<?php

namespace Scherzo;

use Scherzo\Pipeline;
use Scherzo\Container;
use Scherzo\HttpService;
use Scherzo\Router;

class Scherzo {

    protected $defaults = [
        'services' => [
            'http' => HttpService::class,
            'router' => Router::class,
        ],
    ];

    protected $request;

    public function __construct($request = null) {
        $this->request = $request;
    }

    public function run() {

        // Load the config from arguments.
        $config = call_user_func_array('array_merge_recursive', func_get_args());

        // Create a container and add essential services.
        $container = new Container();
        $container->config = $config;
        $container->define($container->config['services']);

        // Add routes.
        $container->router->addRoutes([
            $container->config['routes']
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

        // Execute the pipeline with a provided request (or parse it from globals if null).
        return $next($next, $this->request);
    }
}
