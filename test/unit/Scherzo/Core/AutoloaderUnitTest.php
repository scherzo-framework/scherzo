<?php
/**
 * This file is part of the Scherzo PHP application framework.
 *
 * @link      http://github.com/scherzo-framework/scherzo/
 * @copyright Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license   MIT
**/

namespace Scherzo\Test\Core;

use Exception;

// the class being tested and the class it extends
use Scherzo\Core\Autoloader;
require_once 'classes/Scherzo/Core/Service.php';
require_once 'classes/Scherzo/Core/Autoloader.php';

/**
 * Unit tests for \Scherzo\Autoloader class.
**/
class AutoloaderUnitTest extends \PHPUnit_Framework_Testcase
{

    /**
     * @expectedException         Exception
     * @expectedExceptionMessage  Cannot set nonexistant path "path/does/not/exist"
     *
     * @covers  setNamespace
    **/  
    function test_setNamespace_throws_an_exception_for_a_nonexistant_path()
    {
        $autoloader = new Autoloader(null, null);
        $autoloader->setNamespace('Scherzo', 'path/does/not/exist');
    }

    /**
     * @covers  setNamespace
    **/
    function test_setNamespace_is_chainable()
    {
        $autoloader = new Autoloader(null, null);
        $return = $autoloader->setNamespace('test', __DIR__);
        $this->assertSame($autoloader, $return);
    }

    /**
     * @covers  loadClass
    **/  
    function test_loadClass_loads_a_class_file_if_it_exists()
    {
        $autoloader = new Autoloader(null, null);
        $autoloader->setNamespace('Scherzo', 'classes');
        $result = $autoloader->loadClass('Scherzo\Core\Autoloader');
        $this->assertEquals(realpath('classes/Scherzo/Core/Autoloader.php'), str_replace('/', DIRECTORY_SEPARATOR, $result));
    }

    /**
     * @covers  loadClass
    **/  
    function test_loadClass_silently_fails_if_a_namespace_has_not_been_registered()
    {
        $autoloader = new Autoloader(null, null);
        $result = $autoloader->loadClass('Scherzo\Core\DoesntMatter');
        $this->assertSame(false, $result);
    }

    /**
     * @covers  loadClass
    **/  
    function test_loadClass_silently_fails_if_a_class_file_doesnt_exist()
    {
        $autoloader = new Autoloader(null, null);
        $autoloader->setNamespace('Scherzo', 'classes');
        $result = $autoloader->loadClass('Scherzo\Core\DoesntExist');
        $this->assertSame(false, $result);
    }

    /**
     * @covers  loadClassComposer
    **/  
    function test_loadClassComposer_loads_a_class_file_if_it_exists()
    {
        $autoloader = new Autoloader(null, null);
        $autoloader->setNamespace('Scherzo', 'classes');
        $result = $autoloader->loadClassComposer('Scherzo\Core\Autoloader');
        $this->assertEquals(realpath('classes/Scherzo/Core/Autoloader.php'), str_replace('/', DIRECTORY_SEPARATOR, $result));
    }

    /**
     * @covers  loadClassComposer
    **/  
    function test_loadClassComposer_tries_to_load_Composer_autoloader_if_it_fails()
    {
        $autoloader = new Autoloader(null, null);
        $autoloader->setNamespace('Scherzo', 'classes');
        $result = $autoloader->loadClassComposer('Scherzo\Core\DoesntExist', true);
        $this->assertSame(true, $result);
    }
}
