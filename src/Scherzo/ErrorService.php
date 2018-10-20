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
use Scherzo\HttpException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Process HTTP request and response messages.
 *
 * @package Scherzo
**/
class ErrorService {

    use ServiceTrait;

    /**
     * Middleware to create the request object.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  null      $request  Null because the request hasn't yet been parsed.
    **/
    public function handleErrorsMiddleware(callable $next, $request = null) : Response {
        // Get the response from the next handler in the chain.
        try {
            $response = $next($next, $request);
        } catch (\Throwable $e) {
            if (is_a($e, HttpException::class)) {
                $response = $this->getHttpExceptionResponse($request, $e);
            } else {
                $response = $this->getExceptionResponse($request, $e);
            }
        }
        $response->prepare($request);
        return $response;
    }

    /**
     * Get an HTTP response to an error.
     *
     * @param  Request    $request  The request that caused the exception.
     * @param  Throwable  $e        The exception.
     * @return Response   An appropriate response.
    **/
    protected function getExceptionResponse(Request $request, \Throwable $e) {
        $httpException = new HttpException($e->getMessage());
        return getHttpExceptionResponse($httpException);
    }

    /**
     * Get an HTTP response to an HttpException.
     *
     * @param  Request        $request  The request that caused the exception.
     * @param  HttpException  $e        The exception.
     * @return Response       An appropriate response.
    **/
    protected function getHttpExceptionResponse(Request $request, HttpException $e) {
        return new Response($e->getMessage(), $e->getStatus());
    }
}
