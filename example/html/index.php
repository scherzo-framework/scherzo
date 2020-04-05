<?php

$appPath = '../bootstrap.php';

ini_set('display_errors', 0);

$fullPath = __DIR__.DIRECTORY_SEPARATOR.$appPath;
if ($app = include($fullPath)) {
    $app();
    return;
}

if (isset($_ENV['PHP_ENV']) && $_ENV['PHP_ENV'] === 'production') {
    echo('Could not load bootstrap.');
} else {
    $realPath = realpath(dirname($fullPath));
    if ($realpath) {
        echo(htmlspecialchars('Could not load bootstrap from '.$realPath.'.'));
    } else {
        echo(htmlspecialchars('Could not load bootstrap from '.$appPath
            .' (could not resolve path '.$fullPath.').'));
    }
}
