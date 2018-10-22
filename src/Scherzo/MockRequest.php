<?php

namespace Scherzo;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MockRequest {

    /** @var {Request} The request to send. */
    protected $config;

    /** @var {Request} The request to send. */
    protected $request;

    /** @var {Response} The response received to send. */
    protected $response;

    /** @var {boolean} Set to true when the request has been sent. */
    protected $isSent = false;

    public function __construct(array $request = [], array $config = []) {
        $this->request = $request;
        $this->config = $config;
    }

    protected function send() {
        $request = Request::create(
            $this->request['uri'] ?? '/', // The URI
            $this->request['method'] ?? 'get', // The HTTP method
            $this->request['parameters'] ?? [], // The query (GET) or request (POST) parameters
            $this->request['cookies'] ?? [], // The request cookies ($_COOKIE)
            $this->request['files'] ?? [], // The request files ($_FILES)
            $this->request['server'] ?? [], // The server parameters ($_SERVER)
            $this->request['content'] ?? '' // The raw body data as a string or resource.
        );
        $request->headers->add($this->request['headers'] ?? []);
        $app = new Scherzo($this->config);
        $this->response = $app->run($request);
        $this->isSent = true;
        return $this;
    }

    public function getResponse() {
        if (!$this->isSent) {
            $this->send();
        }
        return $this->response;
    }

    public function getResponseBody() {
        if (!$this->isSent) {
            $this->send();
        }
        return $this->response->getContent();
    }

    public function getResponseStatus() {
        if (!$this->isSent) {
            $this->send();
        }
        return $this->response->getStatusCode();
    }
}
