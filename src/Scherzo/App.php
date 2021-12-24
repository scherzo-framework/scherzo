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

class App
{
    /** @var Container Dependencies. */
    protected $c;

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function run(array $config): void
    {
        // Create a DI container populated from $config.
        $c = new Container($config);
        // Create the application with the container.
        $app = new self($c);
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
            $this->beforeAddRoute($request);
            $this->addRoute($request);
            $this->afterAddRoute($request);
            return $this->executeRoute($request);
        } catch (HttpException $e) {
            $this->logHttpException($e, $request);
            return $this->handleHttpException($e, $request);
        } catch (\Throwable $e) {
                // Turn other errors into HTTP exceptions.
            $this->logError($e, $request);
            return $this->handleError($e, $request);
        }
    }

    // Override the following hooks to add middleware.
    protected function beforeAddRoute(Request $request): void
    {
    }

    protected function afterAddRoute(Request $request): void
    {
    }

    protected function logError(\Throwable $e, Request $request): void
    {
    }

    protected function logHttpException(HttpException $e, Request $request): void
    {
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
        // Could have more information in debug mode e.g.
        // $httpException = (new HttpException($e->__toString()))->setStatusCode(500);
        $httpException = (new HttpException($e->getMessage()))->setStatusCode(500);
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
        $response->setEncodingOptions(0);
        $response->setData(
            ['error' => [
            'title' => $response::$statusTexts[$statusCode],
            'message' => $e->getMessage(),
            ]]
        );
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
        return $request->route->execute($request);
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
