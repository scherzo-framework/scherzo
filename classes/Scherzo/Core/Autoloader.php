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
            $path = $this->prefixes[substr($class, 0, strpos($class, '\\'))];

            // add the path to any sub-namespace
            if ($lastNsPos = strrpos($class, '\\')) {
                $namespace = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $lastNsPos));
                $class = substr($class, $lastNsPos + 1);
                $path .= "$namespace/";
            }
            // add the class name
            $path = "$path$class.php";

            // this is a signal to the error handler
            $ignoreWarning = true;
            if (include_once $path) {
                return $path;
            } else {
                return false;
            }

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Loads the class file for a given class name.
     *
     * This method is registered for Composer installations so that the first
     * time Scherzo's autoload fails, the composer autoloader is registered.
     *
     * @param   string   $class  The fully-qualified class name.
     * @param   boolean  $test   Set to true in unit testing to avoid registering the autoloader.
     * @return  mixed    The path to the class file if it exists otherwise `false`.
    **/
    public function loadClassComposer($class, $test = null)
    {
        $result = $this->loadClass($class);
        // a truthy value will be the path to the class file, so return it
        if ($result) {
            return $result;
        }
        // if this is a test we don't want to change any autoloaders so just show we know what we are doing
        if ($test) {
            return true;
        }
        spl_autoload_unregister(array($this, 'loadClassComposer'));
        spl_autoload_register(array($this, 'loadClass'));
        require_once($this->depends->local->coreVendorDirectory);
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
     * Register Composer loader with SPL autoloader stack.
     *
     * @return  Autoloader  `$this`  (chainable)
    **/
    public function registerComposer()
    {
        spl_autoload_register(array($this, 'loadClassComposer'));
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
            // if the Scherzo namespace has not been set up ScherzoException may not be available
            if (!class_exists('Scherzo\Core\ScherzoException')) {
                require __DIR__.'/ScherzoException.php';
            }
            throw new ScherzoException(strtr(
                'Cannot set nonexistant path ":path" for namespace ":ns"',
                array(':path' => $path, ':ns' => $namespace)
            ));
        } else {
            $this->prefixes[$namespace] = $realpath . DIRECTORY_SEPARATOR;
            return $this;
        }
    }
}
