<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher\Tests;

use PhpStandard\Container\Configurator;
use PhpStandard\Container\Container;
use PhpStandard\EventDispatcher\ListenerProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ListenerProviderTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $configurator = new Configurator();
        $this->container = new Container($configurator);
    }

    public function testGetListenersForEvent(): void
    {
        $provider = new ListenerProvider($this->container);

        $listener = new MockListener();
        $event = new MockEvent();

        $provider
            ->addEventListener(
                MockEvent::class,
                function () {
                },
                ListenerProvider::PRIORITY_LOW
            )
            ->addEventListener('SomeOtherEvent', $listener)
            ->addEventListener(
                MockEvent::class,
                'strlen',
                ListenerProvider::PRIORITY_HIGH
            )
            ->addEventListener(
                MockEvent::class,
                $listener,
                ListenerProvider::PRIORITY_NORMAL
            );

        $this->assertSame(
            3,
            iterator_count($provider->getListenersForEvent($event))
        );

        foreach ($provider->getListenersForEvent($event) as $index => $list) {
            if ($index == 0) {
                $this->assertEquals('strlen', $list);
            } elseif ($index == 1) {
                $this->assertEquals($listener, $list);
            } elseif ($index == 2) {
                $this->assertInstanceOf(
                    \Closure::class,
                    $list
                );
            }
        }
    }
}
