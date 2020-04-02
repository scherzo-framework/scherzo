<?php declare(strict_types=1);
/**
 * Scherzo\Request unit test.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [ISC](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

use Scherzo\Request;

final class TheScherzoRequestClassTest extends \PHPUnit\Framework\TestCase {

    public function testShouldCreateAnInstanceOfItselfFromGlobals(): void {
        $req = Request::createFromGlobals();

        $this->assertInstanceOf(Request::class, $req);
    }

    public function testShouldIdentifyProductionMode(): void {
        $oldEnv = array_key_exists('PHP_ENV', $_ENV) ? $_ENV['PHP_ENV'] : false;

        $req = new Request;

        $_ENV['PHP_ENV'] = 'development';
        $this->assertFalse($req->isProduction());

        $_ENV['PHP_ENV'] = 'production';
        $this->assertTrue($req->isProduction());

        unset($_ENV['PHP_ENV']);
        $this->assertTrue($req->isProduction());

        if ($oldEnv !== false) {
            $_ENV['PHP_ENV'] = $oldEnv;
        }
    }

    public function testShouldAddAProperty(): void {
        $req = new Request;

        $req->property = true;

        $this->assertTrue($req->property);
    }

    public function testShouldAddAnArrayPropertyAsAParameterBag(): void {
        $req = new Request;

        $req->params = [
            'name' => 'someone',
        ];

        $this->assertEquals('someone', $req->params->get('name'));
    }
}
