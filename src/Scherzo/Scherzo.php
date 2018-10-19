<?php

namespace Scherzo;

use Scherzo\Pipeline;
use Scherzo\Container;
use Scherzo\HttpService;
use Scherzo\Router;

class Scherzo {

    protected $container;

    protected $defaults = [
        'services' => [
            'http' => HttpService::class,
            'router' => Router::class,
        ],
        'routes' => [
        ],
    ];

    protected $request;

    public function __construct($request = null) {
        $this->request = $request;
    }

    public function run() {

        try {
            // Errors still not exceptions??
            set_error_handler([$this, 'exception_error_handler']);

            // Load the config from arguments.
            $config = func_get_args();
            array_unshift($config, $this->defaults);
            $config = call_user_func_array('array_merge_recursive', $config);

            // Create a container and add essential services.
            $this->container = new Container();
            $this->container->config = $config;
            $this->container->define($this->container->config['services']);
            print_r($this->container);

            // Add routes.
            $this->container->router->addRoutes($this->container->config['routes']);

            // Build the request pipeline
            $next = new Pipeline($this->container);

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

        } catch (\Throwable $e) {
            try {
                // Handle in development environment.
                if ($config['env'] === 'dev') {
                    $handler = $this->container->get('debug');
                    $handler->handle($e);
                    return $e;
                }
            } catch (\Throwable $ee) {
                throw $ee;
            }
            echo 'Unexpected error';
            return $e;
        }
    }

    function exception_error_handler($severity, $message, $file, $line) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
}
