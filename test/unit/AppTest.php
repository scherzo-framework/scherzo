<?php

declare(strict_types=1);

namespace UnitTest;

use PHPUnit\Framework\TestCase;
use Scherzo\Request;
use Scherzo\Container;
use Scherzo\App;
use TestFixtures\MockController;

final class AppTest extends TestCase
{
    public function testAnAppShouldHaveAVersion(): void
    {
        $this->assertEquals('string', gettype(App::SCHERZO_VERSION));
    }

    public function testShouldRouteAGetRequest(): void
    {
        $routes = [
            ['GET', '/{id:.+}', [MockController::class, 'getIndex']],
        ];
        $app = new App($routes);
        $request = Request::create('/123');

        $response = $app->run($request, false);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            '{"data":{"id":123,"name":"Item 123"}}',
            $response->getContent()
        );
    }

    public function testShouldThrowAnHttpExceptionWhenNotFound(): void
    {
        $routes = [
            ['GET', '/', [MockController::class, 'getIndex']],
        ];
        $app = new App($routes);

        $request = Request::create('/will-not-work');

        $response = $app->run($request, false);

        $this->assertEquals(404, $response->getStatusCode());

        $json = json_decode($response->getContent());
        $error = $json->error;

        // All JSON errors should have a fixed title.
        $this->assertEquals('Not Found', $error->title);
        // All JSON errors should have a descriptive message.
        $this->assertEquals('Could not find /will-not-work', $error->message);
        // All JSON errors should have a status code.
        $this->assertEquals(404, $error->status);
    }

    public function testShouldThrowAnHttpExceptionWhenMethodNotAllowed(): void
    {
        $routes = [
            [['POST', 'PUT'], '/', [MockController::class, 'getIndex']],
        ];
        $app = new App($routes);

        $request = Request::create('/');

        $response = $app->run($request, false);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('POST, PUT', $response->headers->get('allow'));

        $json = json_decode($response->getContent());
        $error = $json->error;

        // All JSON errors should have a fixed title.
        $this->assertEquals('Method Not Allowed', $error->title);
        // All JSON errors should have a descriptive message.
        $this->assertEquals(
            'Method GET not allowed for path /',
            $error->message,
        );
        // All JSON errors should have a status code.
        $this->assertEquals(405, $error->status);
        // Method Not Allowed JSON errors should have extra info.
        // $this->assertEquals(['POST', 'PUT'], $json['info'][]);
        $this->assertEquals(
            (object) ['method' => 'GET', 'path' => '/', 'allowed' => ['POST', 'PUT']],
            $error->info,
        );
    }

    public function testShouldHandleAnError(): void
    {
        $routes = [
            ['GET', '/divide-by-zero', [MockController::class, 'divideByZero']],
        ];
        $app = new App($routes);

        $request = Request::create('/divide-by-zero');

        $response = $app->run($request, false);

        $this->assertEquals(500, $response->getStatusCode());

        $json = json_decode($response->getContent());
        $error = $json->error;

        // All JSON errors should have a fixed title.
        $this->assertEquals('Application error', $error->title);
        // All JSON errors should have a descriptive message.
        $this->assertEquals('Division by zero', $error->message);
        // All JSON errors should have a status code.
        $this->assertEquals(500, $error->status);
    }
}
