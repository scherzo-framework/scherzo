<?php

// declare(strict_types=1);

namespace App;

use Scherzo\App;
use Scherzo\Container;
use Scherzo\HttpException;
use Scherzo\Router;

if (!isset($_ENV['PHP_ENV']) || $_ENV['PHP_ENV'] !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

$loader = require_once __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('App\\', __DIR__.'/src/App');

$container = new Container();

$container->define('log', Logger::class);

$app = new App($container);

$app->routes([
    [Router::GET, '/hello/{name}', Hello::class, 'sayHelloTo'],
    [Router::GET, '/', [Hello::class, 'sayHello']],
    [Router::POST, '/post', function () {}],
]);

$app->use(function (\Throwable $err, $req, $res) use ($container) {
    if (is_a($err, HttpException::class)) {
        // We don't need to log HttpExceptions.
        throw $err;
    }
    $container->log->log('debug', 'Unexpected exception', $err);
    throw new HttpException(500, $err->getMessage(), $err);
});

$app->use(function (HttpException $err, $req, $res) use ($container) {
    $container->log->log('debug', 'Handling an HttpException');

    $res->setStatusCode($err->getStatusCode());
    $code = $err->getCode();
    $status = $err->getStatusCode();
    $info = $err->getInfo();

    $error = [
        'code' => $code,
        'status' => $status,
    ];

    if ($status === 500 && $req->isProduction()) {
        $error['message'] = $code;
    } else {
        $error['message'] = $err->getMessage();
    }

    if ($info) {
        $error['info'] = $info;
    }

    $res->setData();
    $res->setError($error);
});

$app->use(function ($req, $res) use ($container) {
    $res->addJson('meta', 'log', $container->log->getLog());
});

$app->use(function ($req, $res) use ($container) {
    $res->prepare($req);
    $res->send();
});

$app();
