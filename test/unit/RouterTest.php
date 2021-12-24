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

        [$route, $params] = $router->dispatch('GET', '/123');

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
            $router->dispatch('GET', '/will-not-work');
        } catch (HttpException $e) {
            $this->assertEquals(
                404,
                $e->getStatusCode()
            );
            $this->assertEquals(
                'Could not find /will-not-work',
                $e->getMessage()
            );
        }
    }

    public function testShouldThrowAnHttpExceptionWhenMethodNotAllowed(): void
    {
        $router = new Router([
            [['POST', 'PUT'], '/', [MockController::class, 'getIndex']],
        ]);

        try {
            $router->dispatch('GET', '/');
        } catch (HttpException $e) {
            $this->assertEquals(
                405,
                $e->getStatusCode()
            );
            $this->assertEquals(
                ['POST', 'PUT'],
                $e->getAllowedMethods()
            );
            $this->assertEquals(
                'GET not allowed for /',
                $e->getMessage()
            );
        }
    }
}
