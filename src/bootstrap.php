<?php

use Scherzo\Pipeline;
use Scherzo\Container;
use Scherzo\HttpService;
use Scherzo\Router;

require_once(__DIR__.'/../vendor/autoload.php');

$request = isset($request) ? $request : null;

$container = new Container;

$next = new Pipeline($container);

$container->define('http', HttpService::class);
$container->define('router', Router::class);

$container->router->addRoutes([
  ['GET', '{path:/.*}', function ($vars, $request) {
    return $this->http->createResponse('Hello Worlds');
  },
  ]
]);

$next->pushMultiple([
  ['http', 'sendResponseMiddleware'],
  ['http', 'parseRequestMiddleware'],
  ['router', 'matchRouteMiddleware'],
  ['router', 'executeRouteMiddleware'],
]);

return $next($next, $request);
