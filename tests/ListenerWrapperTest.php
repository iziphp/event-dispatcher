<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher\Tests;

use PhpStandard\Container\Configurator;
use PhpStandard\Container\Container;
use PhpStandard\EventDispatcher\ListenerWrapper;
use PhpStandard\EventDispatcher\Priority;
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
        $wrapper = new ListenerWrapper(
            $this->container,
            $listener,
            Priority::NORMAL
        );
        $this->assertSame(Priority::NORMAL, $wrapper->getPriority());
    }

    /** @test */
    public function testGetListener(): void
    {
        $listener = new MockListener();
        $wrapper = new ListenerWrapper(
            $this->container,
            $listener,
            Priority::NORMAL
        );
        $this->assertSame($listener, $wrapper->getListener());
    }
}
