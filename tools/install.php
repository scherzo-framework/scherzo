#!/usr/bin/env php
<?php

ini_set('show_errors', 1);
error_reporting(-1);

$file = 'tools/composer-setup.php';
if (file_exists($file)) {
    unlink($file);
}

// PHPUnit.
$file = file_get_contents('https://phar.phpunit.de/phpunit-9.phar');
file_put_contents('tools/phpunit.phar', $file);

// PHP Code Sniffer.
$file = file_get_contents('https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar');
file_put_contents('tools/phpcbf.phar', $file);

// PHP Code Beautifier.
$file = file_get_contents('https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar');
file_put_contents('tools/phpcs.phar', $file);

// PHP Documentor.
$file = file_get_contents('https://phpdoc.org/phpDocumentor.phar');
file_put_contents('tools/phpdoc.phar', $file);
