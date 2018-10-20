<?php

namespace EndToEnd;

use PHPUnit\Framework\TestCase;
use Scherzo\MockRequest;

use Scherzo\HttpNotFoundException as NotFoundException;

class helloWorldAppTest extends TestCase {

    public function testNoConfigurationGivesNotFoundException() {
        $request = new MockRequest();
        // $this->expectException(NotFoundException::class);
        $this->assertSame(404, $request->getResponseStatus());
    }
}
