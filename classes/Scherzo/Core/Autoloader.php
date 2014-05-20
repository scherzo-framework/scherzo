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
 * PSR-4 class autoloader.
**/
class Autoloader
{
    /**
     * An associative array where the key is a (top level) namespace prefix and
     * the value is the base directory for classes in that namespace.
     *
     * @var array
    **/
    protected $prefixes = array();

    /**
     * Loads the class file for a given class name.
     *
     * This implementation does not check if the file exists before including
     * it for efficiency, which is fine for the autoloading strategy used (each
     * namespace has only one base directory).
     *
     * @param  string  $class The fully-qualified class name.
     * @return mixed The path to the class file if it exists otherwise `false`.
    **/
    public function loadClass($class)
    {

        try {
            // get the root path for the root namespace
            $path  = $this->namespaces[substr($class, 0, strpos($class, '\\'))];

            // add the path to any sub-namespace
            if ($lastNsPos = strrpos($class, '\\')) {
                $namespace = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $lastNsPos));
                $class = substr($class, $lastNsPos + 1);
                $path .= "$namespace/";
            }
            // add the class name
            $path = "$path$class.php";

            include_once $path;
            return $path;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Register loader with SPL autoloader stack.
     *
     * @return  Autoloader  `$this`  (chainable)
    **/
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
        return $this;
    }

    /**
     * Set the path for a namespace.
     *
     * @param  string  $namespace The namespace to set the path for.
     * @param  string  $path      The path to the classes for the namespace.
     * @return  Autoloader  `$this`  (chainable)
    **/
    public function setNamespace($namespace, $path)
    {
        $realpath = realpath($path);
        if ($realpath == '') {
            throw new Exception(strtr(
                'Cannot set nonexistant path ":path" for namespace ":ns"',
                array(':path' => $path, ':ns' => $namespace)
            ));
        } else {
            $this->namespaces[$namespace] = $path . DIRECTORY_SEPARATOR;
            return $this;
        }
    }
}
