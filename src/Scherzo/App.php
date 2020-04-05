<?php declare(strict_types=1);

/**
 * Scherzo application.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [ISC](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

use Scherzo\Router;
use Scherzo\Request;
use Scherzo\Response;

class App extends Router {

    /** @var bool Flag to separate request and response middleware. */
    protected $isAfterRoutes = false;

    /** @var bool Request (before) and response (after) middleware. */
    protected $middleware = [[], []];

    /**
     * Process a request.
     * 
     * @param Request   $req  Pass a request for testing.
     * @param Response  $res  Pass a response for testing.
     * @return self    Chainable.
     */
    public function __invoke(Request $req = null, Response $res = null): self {
        // Use provided request and response (for testing) or create them.
        $req = $req ?: Request::createFromGlobals();
        $res = $res ?: new Response;
        $err = null;

        try {
            // Execute before middleware and dispatch the request.
            $this->processMiddleware($this->middleware[0], $req, $res);
            $this->dispatchMiddleware($req, $res);
        } catch (\Throwable $e) {
            $err = $e;
        }
        // Execute after middleware.
        $this->processMiddleware($this->middleware[1], $req, $res, $err);
        return $this;
    }

    protected function middlewareCanHandleCurrentState($callable, \Throwable $err = null) {
        // If the first parameter accepted by the middleware has a named type, get the name.
        if (is_array($callable)) {
            $fn = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $fn = new \ReflectionFunction($callable);
        }
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
     * Use middleware.
     *
     * @param callable $middleware  The middleware callable.
     * @return self    Chainable.
     */
    public function use($callable, bool $after = null) {
        // Attach before or after routes as appropriate.
        $part = ($after || $this->isAfterRoutes) ? 1 : 0;
        $this->middleware[$part][] = $callable;
        return $this;
    }

    /**
     * Insert the dispatch middleware into the chain.
     *
     * @param callable $middleware  The middleware callable.
     * @return self    Chainable.
     */
    public function useDispatchMiddleware() {
        $this->isAfterRoutes = true;
        return $this;
    }

    public function dispatchMiddleware(Request $req, Response $res): void {
        $content = $this->dispatch($req, $res);
        // The request handler can return an array of data to be sent as JSON...
        if (is_array($content)) {
            $res->json->set('data', $content);
        // ...or a string to be returned as HTML...
        } elseif (is_string($content)) {
            $res->setContent($content);
        }
        // ...or it can set the $response itself.
    }

    protected function processMiddleware($pipeline, $req, $res, \Throwable $err = null) {
        foreach ($pipeline as $middleware) {
            $canHandleCurrentState = $this->middlewareCanHandleCurrentState($middleware, $err);
            if ($canHandleCurrentState) {
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

    function setHttpErrorResponseMiddleware(HttpException $err, Request $req, Response $res): void {
        $this->container->log->log('debug', 'Handling an HttpException');

        $res->setStatusCode($err->getStatusCode());
        $code = $err->getCode();
        $status = $err->getStatusCode();
        $info = $err->getInfo();
    
        $error = [
            'code' => $code,
            'status' => $status,
        ];
    
        if ($status === 500 && $req->isProduction()) {
            $error['message'] = $code;
        } else {
            $error['message'] = $err->getMessage();
        }
    
        if ($info) {
            $error['info'] = $info;
        }
    
        $res->json->set('error', $error);
    }

    function sendResponseMiddleware(Request $req, Response $res): void {
        $res->prepare($req);
        $res->send();
    }
}
