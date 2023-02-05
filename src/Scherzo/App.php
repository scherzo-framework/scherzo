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
use Scherzo\HttpException;
use Scherzo\Router;
use Scherzo\Route;
use Scherzo\Request;
use Scherzo\Response;
use Scherzo\Utils;

class App
{
    public const SCHERZO_VERSION = '0.9.1';

    /** @var Container Dependencies. */
    protected $c;

    public function __construct(array $routes, array $config = [], array $lazy = [])
    {
        // Create a DI container populated from $config.
        $this->c = new Container($config);
        // Add lazy-loaded services.
        foreach ($lazy as $key => $lazyOne) {
            $this->c->lazy($key, $lazyOne);
        }

        // Create a router prepared with the routes.
        $router = new Router($routes);
        $this->c->set('router', $router);
    }

    /**
     * Run the application for the request.
     */
    public function run(Request $request = null, bool $send = true): Response
    {
        // If we haven't been provided one (e.g. by a test), create the request.
        if ($request === null) {
            $request = $this->createRequest();
        }
        try {
            $response = $this->createResponse();
            $this->addRoute($request);
            $this->dispatchRoute($request, $response);
        } catch (HttpException $e) {
            $this->handleHttpException($e, $request, $response);
        } catch (\Throwable $e) {
            $this->handleError($e, $request, $response);
        }
        if ($send) {
            $this->sendResponse($response, $request);
        }
        return $response;
    }

    /**
     * Middleware to add the matching route to a request.
     *
     * @param Request The current request.
     */
    protected function addRoute(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPathInfo();
        $routeInfo = $this->c->get('router')->match($method, $path);

        $route = new Route($this->c, $routeInfo);

        $request->route = $route;
    }

    protected function createRequest(): Request
    {
        return Request::createFromGlobals();
    }

    protected function createResponse(): Response
    {
        return new Response();
    }

    /**
     * Middleware to execute a route.
     *
     * @param Request The current request.
     * @return Response The response (maybe an error response).
     */
    protected function dispatchRoute(Request $request, Response $response): void
    {
        // $route = $request->attributes->get('route');
        $content =  $request->route->dispatch($request, $response);
        switch (gettype($content)) {
            case 'array':
                // JSON data.
                $response->setData($content);
                return;
            case 'string':
                // HTML.
                $response->setContent($content);
                return;
        }
    }

    /**
     * Middleware to handle an unexpected error.
     *
     * @param Request The current request.
     * @return Response An error response.
     */
    protected function handleError(\Throwable $e, Request $request, Response $response): void
    {
        $httpException = (new HttpException($e->getMessage(), $e->getCode(), $e))
            ->setStatusCode(500)
            ->setTitle('Application error');
        $this->handleHttpException($httpException, $request, $response);
    }

    /**
     * Middleware to handle an HTTP Exception.
     *
     * @param Request The current request.
     * @return Response An error response.
     */
    protected function handleHttpException(HttpException $e, Request $request, Response $response): void
    {
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
        if ($this->c->has('debug') && $this->c->get('debug') === true) {
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
        $this->logError($e, $data);
    }

    protected function logError(\Throwable $e, array $info): void
    {
        // Override to log errors.
    }

    /**
     * Middleware to send the response.
     *
     * @codeCoverageIgnore
     * @param Request The current request.
     */
    protected function sendResponse(Response $response, Request $request): void
    {
        $response->prepare($request);
        $response->send();
    }
}
