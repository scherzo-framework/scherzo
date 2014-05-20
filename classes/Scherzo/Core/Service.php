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
 * Optional foundation for a Scherzo service.
 *
 * Most Scherzo services extend this class for convenience.
**/
abstract class Service
{
    /**
     * The dependency injection container.
     *
     * @var  Scherzo\Scherzo  The dependency injection container.
    **/
    protected $depends;

    /**
     * The name the service is registered in the container as.
     *
     * @var  string
    **/
    protected $name;

    /**
     * Final constructor.
     *
     * This is made final to avoid omission of a call to `parent::__construct()`
     * in implementing classes. See `afterConstructor()`.
     *
     * @param  Container  $depends  The dependency injection container.
     * @param  string     $name     The name the service is loaded as.
    **/
    final public function __construct($depends, $name)
    {
        $this->depends = $depends;
        $this->name = $name;
        $this->afterConstructor();
    }

    /**
     * Hook to constructor for implementing classes.
    **/
    protected function afterConstructor()
    {
    }
}
