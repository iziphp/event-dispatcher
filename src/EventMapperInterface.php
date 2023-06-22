<?php

namespace PhpStandard\EventDispatcher;

/** @package PhpStandard\EventDispatcher */
interface EventMapperInterface
{
    /**
     * @param object $event
     * @return iterable<ListenerWrapper>
     */
    public function getListenersForEvent(object $event): iterable;
}
