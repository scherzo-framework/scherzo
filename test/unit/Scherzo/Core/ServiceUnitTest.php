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

// a mock service
require_once 'classes/Scherzo/Core/Service.php';
require_once 'test/unit/mock/Service.php';

/**
 * Unit tests for \Scherzo\Core\Service abstract class.
**/
class ServiceUnitTest extends \PHPUnit_Framework_Testcase
{

    /**
     * @covers  __construct
     * @covers  afterConstructor
    **/
    function test_A_service_can_be_created()
    {
        $testService = new \Scherzo\Mock\Service(null, 'MyName');
        $this->assertEquals('MyName', $testService->getName());
        $this->assertTrue($testService->afterConstructorHookHasRun);
    }
}
