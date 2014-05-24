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

$config = new StdClass;
$config->startTime = microtime(true);
error_reporting(0);

// === NO NEED TO CHANGE ANYTHING BEFORE HERE =================================

// Relative path to this file
    $config->baseUrl = '/scherzo-dev/';

// Leave this out for production or set to 'dev'/'test'/'stage'
    $config->deployment = 'dev';

// path to your application's local settings
    $config->localFile = __DIR__.'/examples/demo/scherzo-demo.local.php';

// for a composer installation set the path to the 'vendor' directory
    // $config->vendorDirectory = __DIR__.'/../live/vendor';
    // $config->localFile = $config->vendorDirectory.'/scherzo/scherzo/examples/demo/scherzo-demo.local.php';

// for a non-composer installation set the path to the Scherzo directory
    $config->scherzoDirectory = __DIR__;

// === NO NEED TO CHANGE ANYTHING AFTER HERE ==================================

// show bootstrap errors in non-production modes
if (isset($config->deployment)) {
    error_reporting(~E_WARNING);
    ini_set('display_errors', 1);
}

if (isset($config->vendorDirectory)) {
    // composer installation
    $ok = include $config->vendorDirectory.'/scherzo/scherzo/classes/Scherzo/Scherzo.php';
} else {
    // "web root" installation
    $ok = include $config->scherzoDirectory.'/classes/Scherzo/Scherzo.php';
}

if ($ok) {
    \Scherzo\Scherzo::bootstrap($config);
    // Scherzo will exit() so should never get here.
}

// fallback if bootstrap not found
if (!headers_sent()) {
    header_remove();
    header('HTTP/1.0 503 Service Unavailable');
    header('Content-Type: text/plain');
}

if (isset($config->deployment)) {
    echo 'Could not find Scherzo.';
} else {
    echo 'This site is closed for maintenance, please come back later.';
}
exit(1);
