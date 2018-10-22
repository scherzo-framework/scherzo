<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/scherzo-framework/scherzo
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017-18 [Paul Bloomfield](https://github.com/scherzo-framework).
**/

namespace Scherzo;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ControllerTrait {

    /** @var Container Dependencies container. */
    protected $container;

    protected $jsonEncodeOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK;

    protected $jsonSafeEncodeOptions = JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT;

    /**
     * Constructor.
     *
     * @param  string  Dependencies container.
    **/
    public function __construct($container = null) {
        $this->container = $container;
        $this->initialize();
    }

    /**
     * Create a response object.
     *
     * @param  Request $request   The request that generated the response.
     * @param  string  $body      The response body.
     * @param  int     $status    Response status if not 200.
     * @param  array   $headers   Any response headers.
     *
     * @return Response A response object.
    **/
    public function createJsonResponse(Request $request = null, array $data, int $status = 200, array $headers = []) : Response {
        $response = new JsonResponse(null, $status, $headers);
        if ($request->getContentType() === 'application/json') {
            $response->setEncodingOptions($this->jsonEncodeOptions);
        } else {
            $response->setEncodingOptions($this->jsonSafeEncodeOptions);
            $response->headers->set('content-type', 'text/plain');
            $response->setCharset('utf-8');
        }
        $response->setData($data);
        return $response;
    }

    /**
     * Create a response object.
     *
     * @param  Request $request   The request that generated the response.
     * @param  string  $body      The response body.
     * @param  int     $status    Response status if not 200.
     * @param  array   $headers   Any response headers.
     *
     * @return Response A response object.
    **/
    public function createResponse(Request $request = null, string $body, int $status = 200, array $headers = []) : Response {
        return new Response($body, $status, $headers);
    }

    /**
     * Initialise the controller.
     *
     * Called by the constructor and to be used in preference to overloading the constructor.
    **/
    protected function initialize() : void {
    }
}
