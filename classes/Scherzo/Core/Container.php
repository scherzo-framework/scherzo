<?php
/**
 * This file is part of the Scherzo PHP application framework.
 *
 * @link      http://github.com/scherzo-framework/scherzo/
 * @copyright Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license   MIT
**/

namespace Scherzo\Core;

use Exception;

/**
 * Scherzo dependency injection container.
 *
 * #### Basic usage
 * Register a class to be lazy-loaded as a service:
 *     $container->register('myServiceName', 'My\Fully\Qualified\ClassName');
 * Get a registered service:
 *     $myService = $container->myServiceName;
 * Load an existing object as a service:
 *     $container->myServiceName = $myObject;
**/
class Container
{
    /** Loaded services. */
    protected $_loaded = array();

    /** Registered services. */
    protected $_registered = array();

    /**
     * Retrieve or lazy-load a service.
     *
     * @param   string  $name The name of the service
     * @return  object  The service.
    **/
    public function __get($name)
    {
        if (!isset($this->_loaded[$name])) {
            $this->load($name);
        }
        return $this->_loaded[$name];
    }

    /**
     * Load a service.
     *
     * @param  string  $name  The name of the service.
     * @param  object  $value The service.
    **/
    public function __set($name, $value)
    {
        if (isset($this->_loaded[$name])) {
            throw new Exception(strtr(
                'Service ":service" cannot be overloaded',
                array(':service' => $name)));
        }
        $this->_loaded[$name] = $value;
    }

    /**
     * List loaded services.
     *
     * @return  array  All loaded services.
    **/
    public function getLoaded()
    {
        return $this->_loaded;
    }

    /**
     * Load defined service.
     *
     * @param  string  $name   The name the service is registered with.
     * @param  string  $alias  Optional alias to use for the service.
    **/
    public function load($name, $alias = null)
    {
        if ($alias === null) {
            $alias = $name;
        }
        if (isset($this->_loaded[$alias])) {
            throw new Exception(strtr(
                'Cannot overload existing service ":alias" with registered service ":service"',
                array(':alias' => $name, ':service' => $name)));
        }
        if (!isset($this->_registered[$name])) {
            throw new Exception(strtr(
                'Cannot load unregistered service ":service"',
                array(':service' => $name)));
        }
        if (class_exists($this->_registered[$name])) {
            $this->_loaded[$alias] = new $this->_registered[$name]($this, $alias);
        } else {
            throw new Exception(strtr(
                'Cannot load service ":service" with non-existant class ":class"',
                array(':service' => $name, ':class' => $this->_registered[$name])));
        }
    }

    /**
     * Register a service.
     *
     * @param  string  $name    The name of the service.
     * @param  string  $service The class name defining the service.
    **/
    public function register($name, $service = null)
    {
        if (is_array($name)) {
            $this->_registered = array_merge($this->_registered, $name);
        }
        $this->_registered[$name] = $service;
    }

}
