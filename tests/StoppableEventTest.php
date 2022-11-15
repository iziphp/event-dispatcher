<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher\Tests;

use PHPUnit\Framework\TestCase;

class StoppableEventTest extends TestCase
{
    /** @test */
    public function testIsPropagationStopped(): void
    {
        $event = new MockEvent();
        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }
}
