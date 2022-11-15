<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher\Tests;

use PhpStandard\Container\Configurator;
use PhpStandard\Container\Container;
use PhpStandard\EventDispatcher\ListenerWrapper;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ListenerWrapperTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $configurator = new Configurator();
        $this->container = new Container($configurator);
    }

    /** @test */
    public function testGetPriority(): void
    {
        $listener = new MockListener();
        $wrapper = new ListenerWrapper($this->container, $listener, 1);
        $this->assertSame(1, $wrapper->getPriority());
    }

    /** @test */
    public function testGetListener(): void
    {
        $listener = new MockListener();
        $wrapper = new ListenerWrapper($this->container, $listener, 1);
        $this->assertSame($listener, $wrapper->getListener());
    }
}
