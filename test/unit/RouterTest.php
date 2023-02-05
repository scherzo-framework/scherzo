<?php

declare(strict_types=1);

namespace UnitTest;

use PHPUnit\Framework\TestCase;
use Scherzo\Test\TestController;
use Scherzo\Router;
use Scherzo\HttpException;

final class RouterTest extends TestCase
{
    public function testShouldRouteAGetRequest(): void
    {
        $router = new Router([
            ['GET', '/{id:.+}', [TestController::class, 'getId']],
        ]);

        [$route, $params] = $router->match('GET', '/123');

        $this->assertSame(
            [TestController::class, 'getId'],
            $route
        );
        $this->assertSame(
            ['id' => '123'],
            $params
        );
    }

    public function testShouldThrowAnHttpExceptionWhenNotFound(): void
    {
        $router = new Router([
            ['GET', '/', [TestController::class, 'getIndex']],
        ]);

        try {
            $router->match('GET', '/will-not-work');
        } catch (HttpException $e) {
            // All JSON errors should have a title.
            $this->assertSame('Not Found', $e->getTitle());
            // All JSON errors should have a descriptive message.
            $this->assertSame(
                'Could not find /will-not-work',
                $e->getMessage()
            );
            // All JSON errors should have a status code.
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function testShouldThrowAnHttpExceptionWhenMethodNotAllowed(): void
    {
        $router = new Router([
            [['POST', 'PUT'], '/', [TestController::class, 'getIndex']],
        ]);

        try {
            $router->match('GET', '/');
        } catch (HttpException $e) {
            // All JSON errors should have a title.
            $this->assertSame('Method Not Allowed', $e->getTitle());
            // All JSON errors should have a descriptive message.
            $this->assertSame(
                'Method GET not allowed for path /',
                $e->getMessage()
            );
            // All JSON errors should have a status code.
            $this->assertSame(405, $e->getStatusCode());
            // Method Not Allowed JSON errors should have extra info.
            $this->assertSame(['POST', 'PUT'], $e->getAllowedMethods());
            $this->assertSame(
                ['method' => 'GET', 'path' => '/', 'allowed' => ['POST', 'PUT']],
                $e->getInfo()
            );
        }
    }
}
