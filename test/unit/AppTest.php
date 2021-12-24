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
    public function testShouldRouteAGetRequest(): void
    {
        $container = new Container([
            'routes' => [
                ['GET', '/{id:.+}', [MockController::class, 'getIndex']],
            ],
        ]);
        $app = new App($container);
        $request = Request::create('/123');

        $response = $app->runRequest($request);

        $this->assertEquals(
            200,
            $response->getStatusCode()
        );
        $this->assertEquals(
            '{"data":{"id":123,"name":"Item 123"}}',
            $response->getContent()
        );
    }

    public function testShouldThrowAnHttpExceptionWhenNotFound(): void
    {
        $container = new Container([
            'routes' => [
                ['GET', '/', [MockController::class, 'getIndex']],
            ],
        ]);
        $app = new App($container);
        $request = Request::create('/will-not-work');

        $response = $app->runRequest($request);

        $this->assertEquals(
            404,
            $response->getStatusCode()
        );
        $this->assertEquals(
            '{"error":{"title":"Not Found","message":"Could not find \/will-not-work"}}',
            $response->getContent()
        );
    }

    public function testShouldThrowAnHttpExceptionWhenMethodNotAllowed(): void
    {
        $container = new Container([
            'routes' => [
                [['POST', 'PUT'], '/', [MockController::class, 'getIndex']],
            ],
        ]);
        $app = new App($container);
        $request = Request::create('/');

        $response = $app->runRequest($request);

        $this->assertEquals(
            405,
            $response->getStatusCode()
        );
        $this->assertEquals(
            'POST, PUT',
            $response->headers->get('allow')
        );
        $this->assertEquals(
            '{"error":{"title":"Method Not Allowed","message":"GET not allowed for \/"}}',
            $response->getContent()
        );
    }

    public function testShouldHandleAnError(): void
    {
        $container = new Container([
            'routes' => [
                ['GET', '/divide-by-zero', [MockController::class, 'divideByZero']],
            ],
        ]);
        $app = new App($container);
        $request = Request::create('/divide-by-zero');

        $response = $app->runRequest($request);

        $this->assertEquals(
            500,
            $response->getStatusCode()
        );
        $this->assertEquals(
            '{"error":{"title":"Internal Server Error","message":"Division by zero"}}',
            $response->getContent()
        );
    }
}
