<?php declare(strict_types=1);

namespace App;

use App\App;

use Scherzo\Container;
use Scherzo\HttpException;
use Scherzo\Router;

if (!isset($_ENV['PHP_ENV']) || $_ENV['PHP_ENV'] !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
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

$app->bootstrap();

if (!isset($request)) {
    $app();
}
