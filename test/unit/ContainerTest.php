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

        $this->assertSame(true, $c->get('value'));
    }

    public function testHasShouldBeTrueIffAnEntryExists(): void
    {
        $c = (new Container())->set('value', true);

        $this->assertTrue($c->has('value'));
        $this->assertFalse($c->has('anotherValue'));
    }

    public function testShouldThrowAPsr11ExceptionWhenAKeyDoesNotExist(): void
    {
        $c = new Container();

        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage("Key 'value' does not exist in this container");

        $c->get('value');
    }
}
