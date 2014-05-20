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

// === NO NEED TO CHANGE ANYTHING BEFORE HERE =================================

// === NO NEED TO CHANGE ANYTHING AFTER HERE ==================================

error_reporting(-1);
ini_set('display_errors', 1);

$config->scherzoDirectory = __DIR__.DIRECTORY_SEPARATOR;

include $config->scherzoDirectory.'classes/Scherzo/Scherzo.php';

\Scherzo\Scherzo::bootstrap($config);
