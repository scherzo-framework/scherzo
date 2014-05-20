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
use Scherzo\Core\Container;
require_once 'classes/Scherzo/Core/Container.php';

// a mock service
require_once 'classes/Scherzo/Core/Service.php';
require_once 'test/unit/mock/Service.php';

/**
 * Unit tests for \Scherzo\Scherzo class.
**/
class ContainerUnitTest extends \PHPUnit_Framework_Testcase
{

    /**
     * @covers  __get
     * @covers  __set
    **/
    function test_A_service_can_be_loaded()
    {
        $container = new Container;
        $testService = new \Scherzo\Mock\Service(null, null);
        $container->testService = $testService;
        $this->assertSame($testService, $container->testService);
    }

    /**
     * @expectedException         Exception
     * @expectedExceptionMessage  Service "testService" cannot be overloaded
     *
     * @covers  __get
     * @covers  __set
    **/  
    function test_An_existing_service_cannot_be_overloaded()
    {
        $container = new Container;
        $container->testService = new \Scherzo\Mock\Service(null, null);
        // should throw an Exception
        $container->testService = new \Scherzo\Mock\Service(null, null);
    }

    /**
     * @covers  register
     * @covers  __set
    **/
    function test_A_service_can_be_registered_and_lazy_loaded()
    {
        $container = new Container;
        $class = 'Scherzo\Mock\Service';
        $container->register('testService', $class);
        $this->assertInstanceOf($class, $container->testService);
    }

    /**
     * @covers  register
     * @covers  load
     * @covers  __set
    **/
    function test_A_service_can_be_loaded_with_an_optional_alias()
    {
        // set up
        $container = new Container;
        $class = 'Scherzo\Mock\Service';
        $container->register('testService', $class);

        // without an alias
        $container->load('testService');
        $this->assertSame('testService', $container->testService->getName());

        // with an alias
        $container->load('testService', 'testAlias');
        $this->assertSame('testAlias', $container->testAlias->getName());
    }

    /**
     * @expectedException         Exception
     * @expectedExceptionMessage  Cannot load unregistered service "testService"
     *
     * @covers  load
    **/
    function test_Trying_to_load_an_unregistered_service_throws_an_exception()
    {
        $container = new Container;
        $container->testService;
    }

    /**
     * @expectedException         Exception
     * @expectedExceptionMessage  Cannot load service "testService" with non-existant class "nonExistantClass"
     *
     * @covers  load
    **/
    function test_Trying_to_load_a_service_registered_with_a_non_existant_class_throws_an_exception()
    {
        $container = new Container;
        $container->register('testService', 'nonExistantClass');
        $container->testService;
    }
}
