<?php declare(strict_types=1);

/**
 * Build on FastRoute to process middleware and dispatch routes.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [ISC](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

use Scherzo\Container;
use Scherzo\Exception;
use Scherzo\Request;
use Scherzo\Response;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;

class Router {
    //HTTP verbs.
    const DELETE = 'DELETE';
    const GET = 'GET';
    const HEAD = 'HEAD';
    const PATCH = 'PATCH';
    const POST = 'POST';
    const PUT = 'PUT';

    /** @var Container Dependencies for injection. */
    protected $container;

    public function __construct(Container $container = null) {
        $this->container = $container === null ? new \StdClass() : $container;
        $this->routeCollector = new RouteCollector(new RouteParser, new DataGenerator);
    }

    /**
     * Attach a route to a request.
     * 
     * @param string   $httpMethod     The HTTP method to match (upper case).
     * @param array    $httpMethod     The HTTP methods to match (upper case).
     * @param string   $path           A path with placeholders to be parsed by FastRoute.
     * @param callable $handler        A callback to be executed if the path is matched.
     * @param string   $handler        The name of a class to be instantiated by the dispatcher
     * @param string   $handlerMethod  The name of a method to call on the handler instance.
     * @return self    Chainable.
     */
    public function route(
        $httpMethod,
        string $path,
        $handler,
        string $handlerMethod = null
    ): self {
        $this->isAfterRoutes = true;
        $this->routeCollector->addRoute(
            $httpMethod,
            $path,
            $handlerMethod === null ? $handler : [$handler, $handlerMethod]
        );
        return $this;
    }

    /**
     * Attach an array of routes to requests.
     * 
     * @param array    $routes     The routes.
     * @return self    Chainable.
     */
    public function routes($routes): self {
        foreach ($routes as $route) {
            call_user_func_array([$this, 'route'], $route);
        }
        return $this;
    }

    /**
     * Handle route found.
     *
     * @param  string   $path    The requested path.
     * @param  string   $method  The requested method.
     * @param  [string] $allowed Allowed methods.
     * @throws HttpException     Always throws a 405 exception.
     */
    protected function dispatchFound(
        $handler,
        array $params,
        Request $req,
        Response $res
    ) {
        $req->set('params', $params, true);
        if (is_array($handler)) {
            $class = $handler[0];
            if (class_exists($class)) {
                $handler[0] = new $class($this->container);
            } else {
                throw new Exception("Handler class '$class' does not exist");
            }
        }

        if (!is_callable($handler)) {
            throw new Exception('Handler is not callable');
        }

        return call_user_func($handler, $req, $res);
    }

    /**
     * Handle method not allowed for route.
     *
     * @param  string   $path    The requested path.
     * @param  string   $method  The requested method.
     * @param  [string] $allowed Allowed methods.
     * @throws HttpException     Always throws a 405 exception.
     */
    protected function dispatchMethodNotAllowed(
        string $path,
        string $method,
        array $allowed,
        Response $res
    ) : void {
        sort($allowed);
        $res->setStatusCode(405);
        $res->headers->set('Allow', implode(',', $allowed));
        throw (new HttpException(405, "$method not allowed for $path"))
            ->setInfo([
                'allowed' => $allowed,
                'method' => $method,
                'path' => $path,
            ])
            ->setCode('MethodNotAllowed');
    }

    /**
     * Handle route not found.
     *
     * @param  string $path  The requested path.
     * @throws HttpException Always throws a 404 exception.
     */
    protected function dispatchNotFound(string $path, Response $res): void {
        $res->setStatusCode(404);
        throw (new HttpException(404, "Route not found for $path"))
            ->setInfo('path', $path)
            ->setCode('RouteNotFound');
    }

    protected function dispatch($req, $res) {
        $dispatcher = new Dispatcher($this->routeCollector->getData());

        $method = $req->getMethod();
        $path = $req->getPathInfo();
        $routeInfo = $dispatcher->dispatch($method, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw $this->dispatchNotFound($path, $res);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw $this->dispatchMethodNotAllowed($path, $method, $routeInfo[1], $res);
            case Dispatcher::FOUND:
                return $this->dispatchFound($routeInfo[1], $routeInfo[2], $req, $res);
        }

        throw (new Exception(
            "Unexpected response {$routeInfo[0]} from dispatcher routing $path $method"
        ));
    }
}
