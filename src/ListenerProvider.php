<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/** @package PhpStandard\EventDispatcher */
class ListenerProvider implements ListenerProviderInterface
{
    /** @var EventMapperInterface[] */
    private array $mappers = [];

    /** @var array<class-string,ListenerWrapper[]> */
    private array $resolved = [];

    /**
     * @inheritDoc
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        yield from $this->getResolvedListeners($event);
    }

    /**
     * @param EventMapperInterface $mapper
     * @return ListenerProvider
     */
    public function addMapper(EventMapperInterface $mapper): self
    {
        $this->mappers[] = $mapper;
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
        if (!array_key_exists($event::class, $this->resolved)) {
            $allWrappers = [];

            foreach ($this->mappers as $mapper) {
                foreach ($mapper->getListenersForEvent($event) as $wrapper) {
                    $allWrappers[] = $wrapper;
                }
            }

            $allWrappers = $this->sortWrappers(...$allWrappers);
            $this->resolved[$event::class] = $allWrappers;
        }

        yield from $this->resolved[$event::class];
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
                return $right->getPriority()->value <=> $left->getPriority()->value;
            }
        );

        return $wrappers;
    }
}
