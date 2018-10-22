<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/scherzo-framework/scherzo
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017-18 [Paul Bloomfield](https://github.com/scherzo-framework).
**/

namespace Scherzo\Router;

use Scherzo\ServiceTrait;

// use Scherzo\RequestInterface as Request;
// use Scherzo\ResponseInterface as Response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Scherzo\Http\HttpNotFoundException as NotFoundException;
use Scherzo\Http\HttpMethodNotAllowedException as MethodNotAllowedException;

// external dependency
use FastRoute\RouteCollector as Collector;
use FastRoute\RouteParser\Std as Parser;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher\GroupCountBased as Matcher;

class Router {

    use ServiceTrait;

    protected $routes;

    /**
     * Initialize - this is called by the parent constructor.
    **/
    public function initialize() {
        $this->routes = new Collector(new Parser, new DataGenerator);
    }

    /**
     * Add a route.
     *
     * @param  string|array  $methods  Methods for the route.
     * @param  string        $pattern  Pattern to match.
     * @param  mixed         $definition  Definition of how to handle the route.
     *
     * @return  $this  Chainable.
    **/
    public function addRoute($method, string $route, $definition) : self {
        $this->routes->addRoute($method, $route, $definition);
        return $this;
    }

    /**
     * Add many routes.
    **/
    public function addRoutes(array $routes) : self {
        $callback = function ($route) {
            call_user_func_array([$this, 'addRoute'], $route);
        };
        array_walk($routes, $callback);
        return $this;
    }

    // TODO: public function dispatch(Request $request, Container $c) : Response {
    public function match(string $method, string $path) {

        $dispatcher = new Matcher($this->routes->getData());
        $routeInfo = $dispatcher->dispatch($method, $path);
        switch ($routeInfo[0]) {
            case Matcher::FOUND:
                return [
                    'method' => $method,
                    'path' => $path,
                    'route' => $routeInfo[1],
                    'vars' => $routeInfo[2],
                ];
            case Matcher::NOT_FOUND:
                throw new NotFoundException(['Could not find a route for :method :path',
                    ':method' => $method, ':path' => $path]);
            case Matcher::METHOD_NOT_ALLOWED:
                throw (new MethodNotAllowedException(['Method :method not allowed for path :path',
                    ':method' => $method, ':path' => $path]))
                    ->setAllowedMethods($routeInfo[1]);
            default:
                throw new RouterException(
                    "Invalid code [$routeInfo[0]] returned from route matcher");
        }
    }

    /**
     * Middleware to match a route.
     *
     * @todo Move this somewhere else - it does not need to be part of either the Router service or
     *       the Http service.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  Request   $request  Null because the request hasn't yet been parsed.
     * @return Response  The response from the rest of the pipeline.
    **/
    public function matchRouteMiddleware(callable $next, $request = null) {
        $http = $this->container->http;
        $router = $this->container->router;

        $method = $http->getRequestMethod($request);
        $path = $http->getRequestPath($request);

        try {
            $route = $router->match($method, $path);
        } catch (RouterException $e) {
            throw new NotFoundException($request);
            return;
        }

        $http->setRequestAttribute($request, 'route', $route);

        return $next($next, $request);
    }

    /**
     * Middleware to dispatch a route.
     *
     * @todo Move this somewhere else - it does not need to be part of either the Router service or
     *       the Http service.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  Request   $request  Null because the request hasn't yet been parsed.
     * @return Response  The response from the rest of the pipeline.
    **/
    public function dispatchRouteMiddleware(callable $next, $request = null) {

        $route = $this->container->http->getRequestAttribute($request, 'route');
        $action = $route['route'];
        $vars = $route['vars'];
        if ($action instanceof \Closure) {
            // Deal with a closure action.
            $response = $action->call($this->container, $request, $vars);
        } elseif (is_array($action)) {
            // Deal with a service.
            $name = $action[0];
            $method = $action[1];
            if ($this->container->has($name)) {
                // Deal with a service.
                $handler = $this->container->get($name);
            } else {
                // Deal with any class (ideally using ControllerTrait).
                $handler = new $name($this->container);
            }
            $response = $handler->$method($request, $vars);
        } else {
            throw new RouterException('Could not dispatch the route');
        }
        if (!($response instanceof Response)) {
            $response = $this->container->http->createResponse($response);
        }
        return $response;
    }
}
