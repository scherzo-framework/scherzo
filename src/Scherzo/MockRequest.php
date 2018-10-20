<?php

namespace Scherzo;

class MockRequest {

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
        $app = new Scherzo($this->request);
        $this->response = $app->run($this->config);
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

}
