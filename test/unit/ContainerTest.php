<?php

declare(strict_types=1);

namespace UnitTest;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Scherzo\Container;

final class ContainerTest extends TestCase
{
    public function testShouldSetAndGetAValue(): void
    {
        $c = (new Container())->set('value', true);

        $this->assertEquals(
            true,
            $c->get('value')
        );
    }

    public function testShouldSetAndGetAClosure(): void
    {
        $c = (new Container())->set('closure', function () {
            return new \StdClass();
        });

        $obj = $c->get('closure');

        $this->assertEquals(
            $obj,
            $c->get('closure')
        );
    }

    public function testAClosureShouldBePassedTheContainer(): void
    {
        $c = (new Container())->set('closure', function (Container $c) {
            $obj = new \StdClass();
            $obj->container = $c;
            return $obj;
        });

        $obj = $c->get('closure');

        $this->assertEquals(
            $obj,
            $c->get('closure')
        );
    }

    public function testHasShouldBeTrueIffAnEntryExists(): void
    {
        $c = (new Container())->set('value', true);

        $this->assertEquals(
            true,
            $c->has('value'),
        );
        $this->assertEquals(
            false,
            $c->has('anotherValue'),
        );
    }

    public function testShouldThrowAPsr11ExceptionWhenAKeyDoesNotExist(): void
    {
        $c = new Container();

        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Identifier "value" is not defined.');

        $c->get('value');
    }
}
