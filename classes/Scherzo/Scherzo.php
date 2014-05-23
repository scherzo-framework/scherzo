<?php
/**
 * This is the main file of the Scherzo Framework.
 *
 * @link      http://github.com/scherzo-framework/scherzo/
 * @copyright Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license   MIT
**/
namespace Scherzo;

use Exception;

/**
 * Scherzo main class.
 *
 * @version v0.1.1-dev
**/
class Scherzo
{
    /** The current version. **/
    const VERSION = '0.1.1-dev';

    /**
     * Bootstrap Scherzo.
     *
     * @param  StdClass  $config  Configuration set in index.php.
    **/
    public static function bootstrap($options)
    {
        try {

            // bootstrap the deployment
            static::bootstrapDeployment($options);

            // first get the local settings so these can be used to configure everything
            $local = static::bootstrapLocal($options);

            // make sure a timezone is set using optional local setting
            static::bootstrapTimezone($local);

            // bootstrap the autoloader
            $autoloader = static::bootstrapAutoloader($local);
            $autoloader->setNamespace('Scherzo', $local->coreScherzoDirectory . 'classes');

            // bootstrap the container for dependency injection
            $container = static::bootstrapContainer($local);

            // load the services just created
            $container->local = $local;
            $container->autoloader = $autoloader;

            // register the other services defined in Local so they can be lazy-loaded
            $container->register($local->coreServices);

            // lazy-load error, exception and shutdown handling
            $container->handleFlow;

        } catch (Exception $e) {

            static::bootstrapError($options, $e);
            // may return here in unit test mode
            return;
        }

        try {
            $autoloader->setNamespace(
                $local->coreApplicationNamespace,
                $local->coreApplicationDirectory . 'classes');
        } catch (Exception $e) {
            throw new \Scherzo\Core\ScherzoException("Application directory \"$local->coreApplicationDirectory\" set in local.php does not exist.");
        }

        // lazy-load and execute the Front Controller
        $container->frontController->execute();

        // controlled shutdown
        $container->handleFlow->shutdown();

        // should never get here
        throw new Exception('Flow controller did not exit');

    }

    /**
     * Bootstrap the deployment mode.
     *
     * @param  StdClass  $initialOptions  Options set in index.php.
    **/
    protected static function bootstrapDeployment($initialOptions)
    {
        if (!isset($initialOptions->deployment)) {
            // production deployment - hide any bootstrapping errors
            error_reporting(0);
            // make sure property exists for later use
            $initialOptions->deployment = null;

            return;
        }

        // non-production: show bootstrapping errors but hide include failures
        error_reporting(~E_WARNING);
        ini_set('display_errors', 1);

        switch ($initialOptions->deployment) {
            case 'dev' :
            case 'coreDev' :
                break;
        }
    }

    /**
     * Bootstrap local configuration.
     *
     * There is no error handling yet so this code needs to fail safe.
     *
     * @param   StdClass            $initialOptions  Options set in index.php.
     * @return  Scherzo\Core\Local  Local settings object.
    **/
    protected static function bootstrapLocal($initialOptions) {

        // load the base class, unless a customised bootstrap has done it already
        if (!class_exists('Scherzo\Core\Local')) {
            include __DIR__.'/Core/Local.php';
        }

        $localFile = $initialOptions->localFile;

        // load the local file (which defines Local extending \Scherzo\Local)
        if (!include $localFile) {
            throw new Exception("local file $localFile does not exist");
        };

        if (isset($initialOptions->deployment)) {
            // not production so insert nonprod before .php
            $nonProdFile = substr($localFile, 0, strlen($localFile) - 3) . 'nonprod.php';
            if (include $nonProdFile) {
                return new \LocalNonProduction($initialOptions);
            } else {
                return new \Local($initialOptions);
            }
        } else {
            // production mode so use the base Local class
            return new \Local($initialOptions);
        }
    }

    /**
     * Deal with unset default timezone.
     *
     * @param  Scherzo\Core\Local  $local  Local settings object.
    **/
    protected static function bootstrapTimezone($local)
    {
        if ($local->coreTimezone) {
            // if it is set explicitly, use it
            date_default_set($local->coreTimezone);
        } else {
            // this is the only way to check it in PHP >= 5.4.0
            if (!ini_get('date.timezone')) {
                date_default_timezone_set($local->coreFallbackTimezone);
            }
        }
    }

    /**
     * Get the autoloader and initialise and register it.
     *
     * @param   Core\Local       $local  Local settings object.
     * @return  Core\Autoloader  Autoload handler.
    **/
    protected static function bootstrapAutoloader($local)
    {
        if (isset($local->coreAutoloaderObject)) {
            $autoloader = $local->coreAutoloaderObject;
        } else {
            require_once __DIR__.'/Core/Autoloader.php';
            $autoloader = new \Scherzo\Core\Autoloader;
        }
        $autoloader->register();
        return $autoloader;
    }

    /**
     * Get the dependency injection container.
     *
     * @param   Core\Local  $local  Local settings object.
     * @return  Scherzo     Dependency injection container.
    **/
    protected static function bootstrapContainer($local)
    {
        // use a custom container if one has been provided
        if (isset($local->coreContainerObject)) {
            return $local->coreContainerObject;
        } else {
            // load the default container
            return new \Scherzo\Core\Container;
        }
    }

    /**
     * Deal with an error in bootstrap.
     *
     * See installation instructions in README.
     *
     * @param  Scherzo\Core\Local  $local  Local settings object.
    **/
    protected static function bootstrapError($options, $e)
    {
        $message = '';

        if (isset($options->deployment)) {
            $message .= 'Scherzo bootstrap error - ' . $e->getMessage()
                . ' in line ' . $e->getLine()
                . ' of ' . $e->getFile() . ".\n\n"
                . "If deployed in production only the following message is displayed:\n\n";
        }

        $message .= 'This site is temporarily closed for maintenance, please come back later.';

        if (isset($options->unittest)) {
            // no headers or exit when run from phpunit
            echo $message;
            return;
        } else {
            if (!headers_sent()) {
                header_remove();
                header('HTTP/1.0 503 Service Unavailable');
                header('Content-Type: text/plain');
            }
            echo $message;
            exit(1);
        }
    }
}
