<?php

namespace Scherzo;

use Scherzo\Pipeline\Pipeline;
use Scherzo\Config\ConfigLoader;
use Scherzo\Container\Container;
use Scherzo\Http\HttpService;
use Scherzo\Router\Router;
use Scherzo\ErrorService\ErrorService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Scherzo {

    protected $container;

    protected $defaults = [
        'app' => [
            'path' => null,
            'env'  => 'prod',
        ],
        'services' => [
            'config' => ConfigLoader::class,
            'http'   => HttpService::class,
            'router' => Router::class,
            'errors' => ErrorService::class,
        ],
        'routes' => [
        ],
    ];

    protected $constructorArgs;

    public function __construct() {
        $this->constructorArgs = func_get_args();
    }

    protected function getConfig() {
        $configs = $this->constructorArgs;
        $config = new ConfigLoader($this->defaults);
        $config->loadEach($configs);
        $config->loadEnv();
        return $config; // ->get();
    }

    public function run(Request $request = null) {

        try {
            // Errors still not exceptions??
            set_error_handler([$this, 'exception_error_handler']);

            // Create a container and add essential services.
            $this->container = new Container();
            $this->container->config = $this->getConfig();
            $this->container->define($this->container->config['services']);

            // Add routes.
            $this->container->router->addRoutes($this->container->config['routes']);

            // Build the request pipeline
            $next = new Pipeline($this->container);

            $next->pushMultiple([
            // Use the HTTP service to send the response.
            ['http', 'sendResponseMiddleware'],

            // Use the HTTP service to parse the request.
            ['http', 'parseRequestMiddleware'],

            // Use the Error service to handle errors.
            ['errors', 'handleErrorsMiddleware'],

            // Use the Router service to match a route.
            ['router', 'matchRouteMiddleware'],

            // Use the Router service to dispatch the route.
            ['router', 'dispatchRouteMiddleware'],
            ]);

            // Execute the pipeline with a provided request (or parse it from globals if null).
            return $next($next, $request);

        } catch (\Throwable $e) {
            try {
                // Handle in development environment.
                $config = $this->container->get('config');
                if ($config['app']['env'] === 'dev' && $this->container->has('debug')) {
                    $handler = $this->container->get('debug');
                    $handler->handle($e);
                    return $e;
                }
            } catch (\Throwable $ee) {
            }
            throw $e;
            echo 'Unexpected error';
            return $e;
        }
    }

    function exception_error_handler($severity, $message, $file, $line) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
}
