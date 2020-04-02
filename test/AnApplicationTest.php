<?php

declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use Scherzo\App;
use Scherzo\Request;
use Scherzo\Response;
use Scherzo\HttpException;

final class AnApplicationTest extends TestCase {
    // Create a simple application.
    protected function app($req) {
        $app = new App();
        $res = new Response;

        // Add route handlers.
        $app->route('GET', '/hello', function ($req) {
            return 'Hello world';
        });

        $app->route('POST', '/hello/{name}', function ($req, $res) {
            return 'Hello '.ucfirst($req->params('name'));
        });

        $app->route('GET', '/hello/{name}', function ($req) {
            return 'Hello '.ucfirst($req->params('name'));
        });

        // Add an exception handler.
        $app->use(function (HttpException $err, $req, $res) {
            // The status code should already be set by the router.
            $res->setContent($err->getMessage());
        });

        // Invoke the app and return the response.
        $app($req, $res);
        return $res;
    }

    public function testShouldRespondToASimpleRequest(): void {
        $req = Request::create('/hello');

        $res = $this->app($req);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Hello world', $res->getContent());
    }

    public function testShouldRespondToARequestWithAPathParameter(): void {
        $req = Request::create('/hello/someone');

        $res = $this->app($req);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Hello Someone', $res->getContent());
    }

    public function testShouldRespondWithANotFoundDocument(): void {
        $req = Request::create('/does-not-exist');

        $res = $this->app($req);

        $this->assertEquals(404, $res->getStatusCode());
        $this->assertEquals('Route not found for /does-not-exist', $res->getContent());
    }

    public function testShouldRespondWithAMethodNotAllowedDocument(): void {
        $req = Request::create('/hello/new-person', 'DELETE');

        $res = $this->app($req);

        $this->assertEquals(405, $res->getStatusCode());
        $this->assertEquals('GET,POST', $res->headers->get('Allow'));
        $this->assertEquals('DELETE not allowed for /hello/new-person', $res->getContent());
    }

    public function testAnEmptyAppThrowsAnException(): void {
        $app = new App();
        $req = new Request;
        $res = new Response;

        $this->expectException(HttpException::class);
        $this->expectExceptionCode('RouteNotFound');

        try {
            $app($req, $res);
        } catch (HttpException $e) {
            $this->assertEquals(404, $e->getStatusCode());
            $this->assertEquals('Route not found for /', $e->getMessage());
            throw $e;
        }
    }
}
