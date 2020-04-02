<?php

declare(strict_types=1);

namespace Scherzo;

use Scherzo\Container;
use Scherzo\Exception;
use Scherzo\RequestInterface as Request;
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

    /** @var bool Flag to separate request and response middleware. */
    protected $isAfterRoutes = false;

    /** @var bool Request (before) and response (after) middleware. */
    protected $middleware = [[], []];

    public function __construct(Container $container = null) {
        $this->container = $container === null ? new \StdClass() : $container;
        $this->routeCollector = new RouteCollector(new RouteParser, new DataGenerator);
    }

    public function __invoke(Request $req, $res) {
        $err = null;
        try {
            $this->processMiddleware($this->middleware[0], $req, $res);
            $this->dispatch($req, $res);
        } catch (\Throwable $e) {
            $err = $e;
        }
        $this->processMiddleware($this->middleware[1], $req, $res, $err);
        return $this;
    }

    /**
     * Attach a route to a request.
     * 
     * @param string   $httpMethod     The HTTP method to match (upper case).
     * @param array    $httpMethod     The HTTP methods to match (upper case).
     * @param string   $path           A string with placeholders describing the path.
     * @param callable $handler        A callback to be executed by the dispatcher.
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

    public function use($callable) {
        if ($this->isAfterRoutes) {
            $this->middleware[1][] = $callable;
        } else {
            $this->middleware[0][] = $callable;
        }
        return $this;
    }

    protected function canHandleCurrentErrorState($callable, \Throwable $err = null) {
        // If the first parameter accepted by the middleware has a named type, get the name.
        $fn = new \ReflectionFunction($callable);
        $params = $fn->getParameters();
        $firstParamType = $params[0]->getType() ?: '';

        if (is_a($firstParamType, \ReflectionNamedType::class)) {
            $firstParamType = $firstParamType->getName();
        }

        if ($err) {
            // Check we can handle errors of this type.
            return is_a($err, $firstParamType);
        } else {
            // Check this is not an error handler.
            return !is_a($firstParamType, \Throwable::class, true);
        }
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
    ) : void {
        $req->setParams($params);
        if (is_array($handler)) {
            $class = $handler[0];
            if (class_exists($class)) {
                $handler[0] = new $class($this->container);
            } else {
                throw new Exception("Handler class '$class' does not exist");
            }
        }

        if (is_callable($handler)) {
            $content = call_user_func($handler, $req, $res);
            // The request handler can return an array of data to be sent as JSON...
            if (is_array($content)) {
                $res->setData($content);
            // ...or a string to be returned as HTML...
            } elseif (is_string($content)) {
                $res->setContent($content);
            }
            // ...or it can set the $response itself.
        } else {
            throw new Exception('Handler is not callable');
        }
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
        $res->headers->set('allow', implode(',', $allowed));
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

    protected function processMiddleware($pipeline, $req, $res, \Throwable $err = null) {
        foreach ($pipeline as $middleware) {
            $canHandleCurrentErrorState = $this->canHandleCurrentErrorState($middleware, $err);
            if ($canHandleCurrentErrorState) {
                try {
                    if ($err) {
                        call_user_func($middleware, $err, $req, $res);
                    } else {
                        call_user_func($middleware, $req, $res);
                    }
                    $err = null;
                } catch (\Throwable $e) {
                    $err = $e;
                }
            }
        }

        // If there is still an error that has not been handled by the pipeline, throw it.
        if ($err) {
            throw $err;
        }
    }
}
