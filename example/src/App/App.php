<?php

namespace App;

use Scherzo\App;
use Scherzo\Container;
use Scherzo\Router;

class App extends ScherzoApp {
    protected $routes = [
        ['log', Logger::class],
    ];
    public function addServices() {
        $this->cont
    }
}


// Create a container and define services.
$container = new Container();
$container->define('log', Logger::class);

// Create an application using the container.
$app = new App($container);

// Define request handling routes.
$app->routes([
    [Router::GET, '/hello', Hello::class, 'sayHello'],
    [Router::GET, '/hello/{name}', Hello::class, 'sayHelloTo'],
    [Router::GET, '/', Index::class, 'getIndexPage'],
    [Router::POST, '/post', function () {}],
]);

// Use some middleware.
$app->useDispatchMiddleware();

$app->use([$container->log, 'errorLoggingMiddleware']);
$app->use([$app, 'setHttpErrorResponseMiddleware']);

// Use middleware to add meta-data to the response.
$app->use(function ($req, $res) use ($container) {
    if (!empty($res->json->keys())) {
        $res->json->set('meta', [
            'log' => $container->log->getLog(),
        ]);
    }
});

$app->use([$app, 'sendResponseMiddleware']);