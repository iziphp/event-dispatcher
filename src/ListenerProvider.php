<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/** @package PhpStandard\EventDispatcher */
class ListenerProvider implements ListenerProviderInterface
{
    /**
     * Listener priority constants
     * Listeners with higher priority number will be called first.
     */
    public const PRIORITY_LOW = 0;
    public const PRIORITY_NORMAL = 50;
    public const PRIORITY_HIGH = 100;

    /**
     * An associative array of the listener wrappers
     * Key is the type of the event, value is the array of the ListenerWrapper.
     *
     * @var array<string,array<ListenerWrapper>>
     */
    private array $wrappers = [];

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    /**
     * @inheritDoc
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        yield from $this->getResolvedListeners($event);
    }


    /**
     * @param string $eventType
     * @param string|callable $listener
     * @param int $priority
     * @return ListenerProvider
     */
    public function addEventListener(
        string $eventType,
        string|callable $listener,
        int $priority = self::PRIORITY_NORMAL
    ): ListenerProvider {
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

    /**
     * Resolve the listeners for event type and
     * return resolved listeners iterable
     *
     * @param object $event
     * @return iterable<callable>
     */
    private function getResolvedListeners(object $event): iterable
    {
        foreach ($this->getWrappers($event) as $wrapper) {
            $listener = $wrapper->getListener();
            yield $listener;
        }
    }

    /**
     * Get original unresolved listener handles match for the event type
     *
     * @param object $event
     * @return iterable<ListenerWrapper>
     */
    private function getWrappers(object $event): iterable
    {
        $allWrappers = [];

        foreach ($this->wrappers as $eventType => $wrappers) {
            if (!$event instanceof $eventType) {
                continue;
            }

            $allWrappers = array_merge($allWrappers, $wrappers);
        }

        $allWrappers = $this->sortWrappers(...$allWrappers);
        yield from $allWrappers;
    }

    /**
     * Sort listener wrappers by descending order priority
     *
     * @param ListenerWrapper $wrappers
     * @return ListenerWrapper[]
     */
    private function sortWrappers(ListenerWrapper ...$wrappers): array
    {
        usort(
            $wrappers,
            function (ListenerWrapper $left, ListenerWrapper $right) {
                return $right->getPriority() <=> $left->getPriority();
            }
        );

        return $wrappers;
    }
}
