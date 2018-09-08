<?php

namespace EndToEnd;

use PHPUnit\Framework\TestCase;

use Scherzo\MockRequest;

class helloWorldAppTest extends TestCase {

  public function testItSaysHello() {

    $request = new MockRequest();

    $this->assertEquals('Hello World', $request->getResponseBody());
  }

}
