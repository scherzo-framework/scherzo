<?php

use Scherzo\Pipeline;
use Scherzo\Container;
use Scherzo\HttpService;

require_once(__DIR__.'/../vendor/autoload.php');

$request = isset($request) ? $request : null;

$container = new Container;

$next = new Pipeline($container);

$container->define('http', HttpService::class);

$next->pushMultiple([
  ['http', 'sendResponseMiddleware'],
  ['http', 'parseRequestMiddleware'],
  function ($next, $request) {
    return $this->http->createResponse('Hello World');
  },
]);

return $next($next, $request);
