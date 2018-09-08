<?php

namespace Scherzo;

class MockRequest {

    /** @var {Request} The request to send. */
    protected $request;

    /** @var {Response} The response received to send. */
    protected $response;

  /** @var {boolean} Set to true when the request has been sent. */
  protected $isSent = false;

    public function __construct(array $request = []) {
        $this->request = $request;
    }

    protected function sendRequest() {
        $request = $this->request;
        $this->response = require(__DIR__.'/../bootstrap.php');
        return $this;
    }

  public function getResponseBody() {
    if (!$this->isSent) {
      $this->sendRequest();
    }
    return $this->response->getContent();
  }

}
