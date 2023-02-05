<?php

declare(strict_types=1);

namespace UnitTest;

use PHPUnit\Framework\TestCase;
use TestFixtures\MockController;
use Scherzo\Router;
use Scherzo\HttpException;

final class RouterTest extends TestCase
{
    public function testShouldRouteAGetRequest(): void
    {
        $router = new Router([
            ['GET', '/{id:.+}', [MockController::class, 'getIndex']],
        ]);

        [$route, $params] = $router->match('GET', '/123');

        $this->assertEquals(
            [MockController::class, 'getIndex'],
            $route
        );
        $this->assertEquals(
            ['id' => '123'],
            $params
        );
    }

    public function testShouldThrowAnHttpExceptionWhenNotFound(): void
    {
        $router = new Router([
            ['GET', '/', [MockController::class, 'getIndex']],
        ]);

        try {
            $router->match('GET', '/will-not-work');
        } catch (HttpException $e) {
            // All JSON errors should have a title.
            $this->assertEquals('Not Found', $e->getTitle());
            // All JSON errors should have a descriptive message.
            $this->assertEquals(
                'Could not find /will-not-work',
                $e->getMessage()
            );
            // All JSON errors should have a status code.
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    public function testShouldThrowAnHttpExceptionWhenMethodNotAllowed(): void
    {
        $router = new Router([
            [['POST', 'PUT'], '/', [MockController::class, 'getIndex']],
        ]);

        try {
            $router->match('GET', '/');
        } catch (HttpException $e) {
            // All JSON errors should have a title.
            $this->assertEquals('Method Not Allowed', $e->getTitle());
            // All JSON errors should have a descriptive message.
            $this->assertEquals(
                'Method GET not allowed for path /',
                $e->getMessage()
            );
            // All JSON errors should have a status code.
            $this->assertEquals(405, $e->getStatusCode());
            // Method Not Allowed JSON errors should have extra info.
            $this->assertEquals(['POST', 'PUT'], $e->getAllowedMethods());
            $this->assertEquals(
                ['method' => 'GET', 'path' => '/', 'allowed' => ['POST', 'PUT']],
                $e->getInfo()
            );
        }
    }
}
