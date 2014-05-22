<?php
/**
 * This file is part of the Scherzo PHP framework.
 *
 * This is the single entry point for an application with the framework
 * installed in the web root (i.e. not via Composer).
 *
 * @link      http://github.com/scherzo-framework/scherzo/
 * @copyright Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license   MIT
**/

// non-composer entry
$config = new StdClass;
$config->startTime = microtime(true);

// === NO NEED TO CHANGE ANYTHING BEFORE HERE =================================

// Relative path to this file
// $config->baseUrl = '/';
$config->baseUrl = '/scherzo-dev/';

// Leave this outfor production or 'dev'/'test'/'stage'
// $options->deployment = 'dev';
$config->deployment = 'dev';

// path to your application's local settings
$config->localFile = __DIR__ . '/local/scherzo-demo.local.php';

// for 'production' installation accessible as www.example.com/myapp/
// $config->vendorDirectory = __DIR__ . '/../../vendor';
// $config->localFile = __DIR__ . '/../../local/scherzo-demo.local.php';

// === NO NEED TO CHANGE ANYTHING AFTER HERE ==================================

error_reporting(-1);
ini_set('display_errors', 1);

$config->scherzoDirectory = __DIR__.DIRECTORY_SEPARATOR;

if (include $config->scherzoDirectory.'classes/Scherzo/Scherzo.php') {

    \Scherzo\Scherzo::bootstrap($config);

} else {

    // fallback if bootstrap not found
    if (!headers_sent()) {
        header_remove();
        header('HTTP/1.0 503 Service Unavailable');
        header('Content-Type: text/plain');
    }
    if ($options->deployment === null) {
        echo 'This site is closed for maintenance, please come back later.';
    } else {
        echo 'Could not find bootstrap.';
    }
    exit(1);
}
