<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher\Tests;

use PhpStandard\Container\Configurator;
use PhpStandard\Container\Container;
use PhpStandard\EventDispatcher\EventDispatcher;
use PhpStandard\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class EventDispatcherTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $configurator = new Configurator();
        $this->container = new Container($configurator);
    }

    /** @test */
    public function canDispatch(): void
    {
        $provider = new ListenerProvider($this->container);

        $provider->addEventListener(
            MockEvent::class,
            MockListener::class
        );

        $dispatcher = new EventDispatcher($provider);
        $event = $dispatcher->dispatch(new MockEvent());

        $this->assertTrue($event->isPropagationStopped());
    }
}
