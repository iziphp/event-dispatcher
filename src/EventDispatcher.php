<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/** @package PhpStandard\EventDispatcher */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @param ListenerProviderInterface $listenerProvider
     * @return void
     */
    public function __construct(
        private ListenerProviderInterface $listenerProvider
    ) {
    }

    /**
     * @inheritDoc
     */
    public function dispatch(object $event)
    {
        $isStoppable = $event instanceof StoppableEventInterface;

        if ($isStoppable && $event->isPropagationStopped()) {
            return $event;
        }

        $listeners = $this->listenerProvider->getListenersForEvent($event);
        foreach ($listeners as $listener) {
            $listener($event);

            /** @var StoppableEventInterface $event */
            if ($isStoppable && $event->isPropagationStopped()) {
                break;
            }
        }

        return $event;
    }
}
