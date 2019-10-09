<?php

declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use Scherzo\App;
use Scherzo\Request;
use Scherzo\Response;
use Scherzo\HttpException;


final class FunctionalTestsTest extends TestCase {
    protected function getResponse($req) {
        $app = new App();
        $res = new Response;
        $app->get('/hello', function ($req) {
            return 'Hello world';
        });
        $app->get('/hello/{name}', function ($req) {
            return 'Hello '.$req->params('name');
        });

        $app->use(function (\Throwable $err, $req, $res) {
            $res->setStatusCode(404);
        });

        $app->use(function ($req, $res) {
            $res->send();
        });

        $app($req, $res);
    }

    public function testAnEmptyAppThrowsAnException() : void {
        $app = new App();
        $req = new Request;
        $res = new Response;

        $this->expectException(HttpException::class);
        $this->expectExceptionCode('RouteNotFound');

        $app($req, $res);
    }

    public function testAnAppWithAnErrorHandlerReturnsAnErrorDocument() : void {
        $app = new App();
        $req = new Request;
        $res = new Response;

        // We need to set at least one route so middleware is handled after routing.
        $app->get('', function () {});

        $app->use(function (\Throwable $err, $req, $res) {
            $res->setStatusCode(404);
        });

        $app($req, $res);

        $this->assertEquals(404, $res->getStatusCode());

        // $this->assertEquals($req->getPathInfo(), '/');
        // $this->assertEquals($res->statusCode, 404);
    }

    public function testASupportedRequestSucceeds() : void {
        $app = new App();
        $req = new Request;
        $res = new Response;

        $app->get('/', function ($req, $res) {
            return ['message' => 'Hello'];
        });

        $app->use(function (\Throwable $err, $req, $res) {
            $res->setStatusCode(404);
        });

        $app($req, $res);

        $this->assertEquals(200, $res->getStatusCode());

        // $this->assertEquals($req->getPathInfo(), '/');
        // $this->assertEquals($res->statusCode, 404);
    }
}
