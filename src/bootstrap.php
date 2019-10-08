<?php

// declare(strict_types=1);

namespace Scherzo;

use Scherzo\App;
use Scherzo\Container;
use Scherzo\HttpException;

if (!isset($_ENV['PHP_ENV']) || $_ENV['PHP_ENV'] !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

require_once __DIR__.'/../vendor/autoload.php';

class Hello {
    public function sayHelloTo($req) {
        $name = $req->params('name');
        if ($name === 'nobody') {
            throw (new HttpException(404, "Cannot find $name to say hello to"))
                ->setInfo('name', $name)
                ->setCode('PersonNotFound');
        }
        return ['message' => 'Hello', 'name' => $name];
    }

    public function sayHello() {
        return ['message' => 'Hello World'];
    }
}

class Logger {
    protected $entries = [];

    function log(string $type, string $message, array $info = null) : self {
        $this->entries[] = $info === null ? [$type, $message] : [$type, $message, $info];
        return $this;
    }

    function getLog() : array {
        return $this->entries;
    }
}

$container = new Container();

$container->define('log', Logger::class);

$app = new App($container);

$app->use(function ($req) use ($container) {
    $wantedKeys = [
        'HTTP_HOST' => 'host',
        'HTTP_REFERER' => 'referer',
        'HTTP_USER_AGENT' => 'userAgent',
        'REQUEST_TIME' => 'requestTimeInt',
        'REQUEST_TIME_FLOAT' => 'requestTime',
        'REMOTE_ADDR' => 'ip',
    ];

    $reqServer = $req->server->all();
    $server = [];
    foreach ($wantedKeys as $oldKey => $newKey) {
        if (isset($reqServer[$oldKey])) {
            $server[$newKey] = $reqServer[$oldKey];
        }
    }
    if (!isset($server['requestTime'])) {
        $server['requestTime'] = isset($server['requestTimeInt']) ? $server['requestTimeInt'] : time();
    }
    unset($server['requestTimeInt']);

    $info = [
        'method' => $req->getMethod(),
        'path' => $req->getPathInfo(),
        // 'post' => $req->request->all(),
        'query' => $req->query->all(),
        // 'server' => $reqServer,
        'server' => $server,
        'files' => $req->files->keys(),
        'cookies' => $req->cookies->keys(),
        'headers' => $req->headers->keys(),
    ];
    $container->log->log('info', 'Handling a request', $info);
});

$app->get('/hello/{name}', [Hello::class, 'sayHelloTo'])
    ->get('/', [Hello::class, 'sayHello'])
    ->post('/post', function () {});

$app->use(function (\Throwable $err, $req, $res) use ($container) {
    if (is_a($err, HttpException::class)) {
        throw $err;
    }
    $container->log->log('debug', 'Logging an Exception');
    throw $err;
});

$app->use(function (\Throwable $err, $req, $res) use ($container) {

    if (is_a($err, HttpException::class)) {
        throw $err;
    }
    $container->log->log('debug', 'Handling an Exception');
    throw new HttpException(500, $err->getMessage());
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
