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

// the class being tested
use Scherzo\Core\Autoloader;
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
        $autoloader = new Autoloader;
        $autoloader->setNamespace('Scherzo', 'path/does/not/exist');
    }

    /**
     * @covers  setNamespace
    **/
    function test_setNamespace_is_chainable()
    {
        $autoloader = new Autoloader;
        $return = $autoloader->setNamespace('test', __DIR__);
        $this->assertSame($autoloader, $return);
    }

    /**
     * @covers  setNamespace
     * @covers  loadClass
    **/  
    function test_loadClass_loads_a_class_file_if_it_exists()
    {
        $autoloader = new Autoloader;
        $autoloader->setNamespace('Scherzo', 'classes');
        $result = $autoloader->loadClass('Scherzo\Core\Autoloader');
        $this->assertEquals('classes/Scherzo/Core/Autoloader.php', str_replace('\\', '/', $result));
    }

    /**
     * @covers  setNamespace
     * @covers  loadClass
    **/  
    function test_loadClass_silently_fails_if_a_class_file_doesnt_exist()
    {
        $autoloader = new Autoloader;
        $autoloader->setNamespace('Scherzo', 'classes');
        $result = $autoloader->loadClass('Scherzo\Core\DoesntExist');
        $this->assertSame(false, $result);
    }
}
