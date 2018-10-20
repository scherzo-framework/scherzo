<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo;

use Scherzo\ServiceTrait;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Process HTTP request and response messages.
 *
 * @package Scherzo
**/
class HttpService {

    use ServiceTrait;

    /**
     *
    **/
    public function getRequestAttribute(Request $request, string $name) {
        return $request->attributes->get($name);
    }

    /**
     *
    **/
    public function setRequestAttribute(Request $request, string $name, $value) : self {
        $request->attributes->set($name, $value);
        return $this;
    }

    /**
     * Get the HTTP request method from a request.
     *
     * @param  Request $request  A request object.
     * @return string  The request method.
    **/
    public function getRequestMethod(Request $request) : string {
        return $request->getMethod();
    }

    /**
     *
    **/
    public function getRequestBasePath(Request $request) : string {
        return $request->getBasePath();
    }

    /**
     *
    **/
    public function getRequestPath(Request $request) : string {
        return $request->getPathInfo();
    }

    /**
     * Create a response object.
     *
     * @param  string  $body      The response body.
     * @param  int     $status    Response status if not 200.
     * @param  array   $headers   Any response headers.
     *
     * @return Response A response object.
    **/
    public function createResponse(string $body, int $status = 200, array $headers = []) : Response {
        return new Response($body, $status, $headers);
    }

    /**
     * Middleware to create the request object.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  null      $request  Null because the request hasn't yet been parsed.
    **/
    public function parseRequestMiddleware(callable $next, $mockRequest = null) : Response {
        // create the request - it all starts here
        if ($mockRequest === null) {
            // Deal with a normal call.
            $request = Request::createFromGlobals();
        } else if (is_a($mockRequest, Request::class)) {
            $request = $mockRequest;
        } else {
            throw new \Exception('Invalid request passed to parseRequestMiddleware');
        }
        // return the response from the next handler in the chain
        $response = $next($next, $request);
        $response->prepare($request);
        return $response;
    }

    /**
     * Middleware to send the response.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  null      $request  The current request.
    **/
    public function sendResponseMiddleware(callable $next, $request = null) : Response {
        if ($request === null) {
            // This is a normal HTTP request so get the response and send it.
            $response = $next($next, $request);
            $response->send();
        } else {
            // This is a mock request so do not send the response.
            $response = $next($next, $request);
        }
        return $response;
    }

}
