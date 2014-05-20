<?php
/**
 * This is the main file of the Scherzo Framework.
 *
 * @copyright Copyright (c) 2014 MrAnchovy http://www.mranchovy.com/
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
    public static function bootstrap($config)
    {
        $scherzo = new static();
        $scherzo->bootstrapAutoloader($config);

        echo '<h3>Roadmap to v0.1</h3>';
        echo '<ul>';
        echo '<li>Bootstrap</li>';
        echo '<li><del>Autoloader (Quick Start install)</del></li>';
        echo '<li>Autoloader (Composer install)</li>';
        echo '<li><del>Dependency injection container</del></li>';
        echo '<li>Errors, exceptions and shutdown</li>';
        echo '<li>Debug</li>';
        echo '<li>FrontController</li>';
        echo '<li>Request</li>';
        echo '<li>HttpRequest</li>';
        echo '<li>ErrorController</li>';
        echo '<li>DefaultController</li>';
        echo '<li>HttpResponse</li>';
        echo '</ul>';

        echo '<h3>Roadmap to v0.9</h3>';
        echo '<ul>';
        echo '<li>Logging</li>';
        echo '<li>Views</li>';
        echo '<li>Twig</li>';
        echo '<li>Filestore</li>';
        echo '<li>Sessions</li>';
        echo '</ul>';
    }

    /**
     * Bootstrap the class autoloader.
     *
     * @param   \StdClass  $config  Configuration set in index.php.
     * @return  \Scherzo\Core\Autoloader  The autoloader object.
    **/
    protected function bootstrapAutoloader($config)
    {
        require_once $config->scherzoDirectory.'/classes/Scherzo/Core/Autoloader.php';
        return (new \Scherzo\Core\Autoloader)->register();
    }

}
