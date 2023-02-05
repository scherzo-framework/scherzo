<?php

declare(strict_types=1);

namespace UnitTest;

use PHPUnit\Framework\TestCase;
use Scherzo\Request;
use Scherzo\App;
use Scherzo\Test\TestController;

final class AppTest extends TestCase
{
    public function testAnAppShouldHaveAVersion(): void
    {
        $this->assertSame('string', gettype(App::SCHERZO_VERSION));
    }

    public function testShouldHandleAJsonGetRequest(): void
    {
        $routes = [
            ['GET', '/{id:.+}', [TestController::class, 'getId']],
        ];
        $app = new App($routes);
        $request = Request::create('/123');

        $response = $app->run($request, false);

        $this->assertSame('', $response->getContent());
        $response->prepare($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('{"data":{"id":123,"name":"Item 123"}}', $response->getContent());
    }

    public function testShouldHandleAResponseModifyingRequest(): void
    {
        $routes = [
            ['GET', '/{id:.+}', [TestController::class, 'getResponseModifier']],
        ];
        $app = new App($routes);
        $request = Request::create('/123');

        $response = $app->run($request, false);
        $response->prepare($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('APPLICATION_SECRET', $response->headers->get('x-api-key'));
        $this->assertSame('{"data":{"id":123,"name":"Item 123"}}', $response->getContent());
    }

    public function testShouldHandleAnHtmlGetRequest(): void
    {
        $routes = [
            ['GET', '/{id:.+}', [TestController::class, 'getIndex']],
        ];
        $app = new App($routes);
        $request = Request::create('/123');

        $response = $app->run($request, false);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello 123', $response->getContent());
        $this->assertNull($response->headers->get('Content-Type'));
        $response->prepare($request);
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function testShouldHandleANotFoundRequest(): void
    {
        $routes = [
            ['GET', '/', [TestController::class, 'getIndex']],
        ];
        $app = new App($routes);

        $request = Request::create('/will-not-work');

        $response = $app->run($request, false);
        $response->prepare($request);

        $this->assertSame(404, $response->getStatusCode());

        $json = json_decode($response->getContent());
        $error = $json->error;

        // All JSON errors should have a fixed title.
        $this->assertSame('Not Found', $error->title);
        // All JSON errors should have a descriptive message.
        $this->assertSame('Could not find /will-not-work', $error->message);
        // All JSON errors should have a status code.
        $this->assertSame(404, $error->status);
    }

    public function testShouldThrowAnHttpExceptionWhenMethodNotAllowed(): void
    {
        $routes = [
            [['POST', 'PUT'], '/', [TestController::class, 'getIndex']],
        ];
        $app = new App($routes);

        $request = Request::create('/');

        $response = $app->run($request, false);
        $response->prepare($request);

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('POST, PUT', $response->headers->get('allow'));

        $json = json_decode($response->getContent());
        $error = $json->error;

        // All JSON errors should have a fixed title.
        $this->assertSame('Method Not Allowed', $error->title);
        // All JSON errors should have a descriptive message.
        $this->assertSame(
            'Method GET not allowed for path /',
            $error->message,
        );
        // All JSON errors should have a status code.
        $this->assertSame(405, $error->status);
        // Method Not Allowed JSON errors should have extra info.
        // $this->assertSame(['POST', 'PUT'], $json['info'][]);
        $this->assertEquals(
            (object) ['method' => 'GET', 'path' => '/', 'allowed' => ['POST', 'PUT']],
            $error->info,
        );
    }

    public function testShouldHandleAnError(): void
    {
        $routes = [
            ['GET', '/divide-by-zero', [TestController::class, 'divideByZero']],
        ];
        $app = new App($routes);

        $request = Request::create('/divide-by-zero');

        $response = $app->run($request, false);
        $response->prepare($request);

        $this->assertSame(500, $response->getStatusCode());

        $json = json_decode($response->getContent());
        $error = $json->error;

        // All JSON errors should have a fixed title.
        $this->assertSame('Application error', $error->title);
        // All JSON errors should have a descriptive message.
        $this->assertSame('Division by zero', $error->message);
        // All JSON errors should have a status code.
        $this->assertSame(500, $error->status);
    }
}
