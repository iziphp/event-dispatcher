<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher\Tests;

/** @package PhpStandard\EventDispatcher\Tests */
class MockListener
{
    /**
     * @param MockEvent $event 
     * @return MockEvent 
     */
    public function __invoke(MockEvent $event): MockEvent
    {
        $event->stopPropagation();
        return $event;
    }
}
