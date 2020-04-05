<?php declare(strict_types=1);

namespace App;

use Scherzo\App;
use Scherzo\Container;
use Scherzo\Router;

error_reporting(E_ALL);
ini_set('display_errors', '1');

$loader = require_once __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('App\\', __DIR__.'/src/App');

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

// Return the app so it can be run.
return $app;
