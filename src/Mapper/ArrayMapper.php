<?php

namespace PhpStandard\EventDispatcher\Mapper;

use PhpStandard\EventDispatcher\EventMapperInterface;
use PhpStandard\EventDispatcher\ListenerWrapper;
use PhpStandard\EventDispatcher\Priority;
use Psr\Container\ContainerInterface;

class ArrayMapper implements EventMapperInterface
{
    /** @var array<class-string,array<ListenerWrapper>>*/
    private array $wrappers = [];
    // private array $resolved = [];s

    public function __construct(
        private ContainerInterface $container
    ) {
    }

    /** @inheritDoc */
    public function getListenersForEvent(object $event): iterable
    {
        // if (!array_key_exists($event::class, $this->resolved)) {
        // $allWrappers = [];
        foreach ($this->wrappers as $eventType => $wrappers) {
            if (!$event instanceof $eventType) {
                continue;
            }

            yield from $wrappers;

            // $allWrappers = array_merge($allWrappers, $wrappers);
        }

        // $this->resolved[$event::class] = $allWrappers;
        // }

        // yield from $this->resolved[$event::class];
    }

    /**
     * @param class-string $eventType
     * @param string|callable $listener
     * @param Priority $priority
     * @return ArrayMapper
     */
    public function addEventListener(
        string $eventType,
        string|callable $listener,
        Priority $priority = Priority::NORMAL
    ): self {
        if (!isset($this->wrappers[$eventType])) {
            $this->wrappers[$eventType] = [];
        }

        $this->wrappers[$eventType][] = new ListenerWrapper(
            $this->container,
            $listener,
            $priority
        );

        return $this;
    }
}
