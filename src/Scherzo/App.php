<?php

/**
 * Scherzo application flow.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2021 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use Scherzo\Container;
use Scherzo\Exception;
use Scherzo\HttpException;
use Scherzo\Router;
use Scherzo\Route;
use Scherzo\Request;
use Scherzo\Response;
use Scherzo\Utils;

class App
{
    public const SCHERZO_VERSION = '0.9.0-dev';

    /** @var Container Dependencies. */
    protected $c;

    protected $isProduction = true;

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function run(array $config, $lazy = []): void
    {
        // Create a DI container populated from $config.
        $c = new Container($config);
        foreach ($lazy as $key => $lazyOne) {
            $c->lazy($key, $lazyOne);
        }
        // Create the application with the container.
        $app = new static($c);
        // Parse the HTTP request.
        $request = Request::createFromGlobals();
        // Run the app with the request and send the response.
        $response = $app->runRequest($request);
        $app->sendResponse($response);
    }

    /**
     * Run the application for the request.
     */
    public function runRequest(Request $request): Response
    {
        try {
            $this->addRoute($request);
            return $this->executeRoute($request);
        } catch (HttpException $e) {
            return $this->handleHttpException($e, $request);
        } catch (\Throwable $e) {
            return $this->handleError($e, $request);
        }
    }

    /**
     * Middleware to add the matching route to a request.
     *
     * @param Request The current request.
     */
    protected function addRoute(Request $request): void
    {
        // Build the routes.
        $routes = $this->c->get('routes');
        $router = new Router($routes);
        $method = $request->getMethod();
        $path = $request->getPathInfo();
        $routeInfo = $router->dispatch($method, $path);
        $route = new Route($this->c, $routeInfo);

        $request->route = $route;
    }

    /**
     * Middleware to handle an unexpected error.
     *
     * @param Request The current request.
     * @return Response An error response.
     */
    protected function handleError(\Throwable $e, Request $request): Response
    {
        if (!is_a($e, Exception::class)) {
            $e = new Exception($e->getMessage(), 0, $e);
            $e->setTitle('Application error');
        }

        $httpException = (new HttpException($e->getMessage(), $e->getCode(), $e))
            ->setStatusCode(500)
            ->setTitle($e->getTitle());
        return $this->handleHttpException($httpException, $request);
    }

    /**
     * Middleware to handle an HTTP Exception.
     *
     * @param Request The current request.
     * @return Response An error response.
     */
    protected function handleHttpException(HttpException $e, Request $request): Response
    {
        $response = new Response();
        $statusCode = $e->getStatusCode();
        // Don't create safe HTML, all responses are sent as JSON.
        $response->setStatusCode($statusCode);
        // print_r($this);
        // throw($e);
        $data = [
            'title' => $e->getTitle() ?? $response::$statusTexts[$statusCode],
            'message' => $e->getMessage(),
            'status' => $statusCode,
        ];
        $info = $e->getInfo();
        if ($info) {
            $data['info'] = $info;
        }
        if (!$this->isProduction) {
            $debug = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ];
            $previous = $e->getPrevious();
            if ($previous) {
                $debug['previous'] = [
                    'title' => Utils::getClass($previous),
                    'message' => $previous->getMessage(),
                    'file' => $previous->getFile(),
                    'line' => $previous->getLine(),
                    'trace' => $previous->getTrace(),
                ];
            }
            $data['debug'] = $debug;
        }
        $response->setData(['error' => $data]);
        if ($statusCode === 405) {
            $response->headers->set('Allow', implode(', ', $e->getAllowedMethods()));
        }
        return $response;
    }

    /**
     * Middleware to execute a route.
     *
     * @param Request The current request.
     * @return Response An error response.
     */
    protected function executeRoute(Request $request): Response
    {
        // $route = $request->attributes->get('route');
        return $request->route->dispatch($request);
    }

    /**
     * Middleware to send the response.
     *
     * @codeCoverageIgnore
     * @param Request The current request.
     */
    protected function sendResponse(Response $response): void
    {
        $response->send();
    }
}
