<?php
/**
 * This file is part of the Scherzo PHP application framework.
 *
 * @link      http://github.com/scherzo-framework/scherzo/
 * @copyright Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license   MIT
**/

namespace Scherzo\Core;

use Exception, Scherzo\Core\ScherzoException;

/**
 * Local settings base class.
**/
class Local
{
    /** Initial options set in `index.php`. */
    protected $coreInitialOptions;

    /** The application directory (set in the application's local.php). */
    public $coreApplicationDirectory;

    /** The application namespace (set in the application's local.php). */
    public $coreApplicationNamespace;

    /** Set this to an object in afterConstructor to override the Scherzo autoloader. */
    public $coreAutoloaderObject;

    /** Set this to an object in afterConstructor to override the Scherzo container. */
    public $coreContainerObject;

    /** The controller used to display 404 etc. errors. */
    public $coreErrorController = '\Scherzo\Core\ErrorController';

    /** Default services - do not modify this, use `$coreServices` instead. */
    private $coreDefaultServices = array(
        'frontController' => 'Scherzo\Core\FrontController',
        'handleFlow'      => 'Scherzo\Core\Flow',
        'httpRequest'     => 'Scherzo\Core\HttpRequest',
        'cliRequest'      => 'Scherzo\Core\CliRequest',
        'testRequest'     => 'Scherzo\Core\TestRequest',
        'httpResponse'    => 'Scherzo\Core\HttpResponse',
    );

    /** Additional/overridden services. */
    public $coreServices = array();

    /** Debugging service - this is only made available in dev mode. */
    public $coreDebugService = 'Scherzo\Core\Debug';

    /** Force the default timezone. */
    public $coreTimezone;

    /** The timezone to use if it is not forced or set in php.ini. */
    public $coreFallbackTimezone = 'UTC';

    final public function __construct($init)
    {
        $this->coreInitialOptions    = $init;
        $this->coreBaseUrl           = isset($init->baseUrl)    ? $init->baseUrl    : '';
        $this->coreStartTime         = isset($init->startTime)  ? $init->startTime  : microtime(true);
        $this->coreDeployment        = isset($init->deployment) ? $init->deployment : null;
        $this->coreServices          = array_merge($this->coreDefaultServices, $this->coreServices);
        
        $this->coreScherzoDirectory  = realpath(
            isset($init->scherzoDirectory)
                ? $init->scherzoDirectory
                : __DIR__.'/../../..') . DIRECTORY_SEPARATOR;

        if ($this->coreDeployment == 'dev') {
            $this->coreServices['debug'] = $this->coreDebugService;
        }

        $this->coreApplicationDirectory
            = realpath(dirname($init->localFile)
            . $this->coreApplicationDirectory)
            . DIRECTORY_SEPARATOR;

        $this->afterConstructor();
    }

    public function afterConstructor()
    {
    }

    public function getInitialOption($name, $default = null)
    {
        return property_exists($this->coreInitialOptions, $name) ? $this->coreInitialOptions->$name : $default;
    }

    public function isProduction()
    {
        return $this->coreDeployment === null;
    }

}
