<?php

namespace PhpStandard\EventDispatcher\Attributes;

use Attribute;
use PhpStandard\EventDispatcher\Priority;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Subscribe
{
    /**
     * @param class-string $eventType FQCN of the event class
     * @param Priority $priority
     * @return void
     */
    public function __construct(
        public readonly string $eventType,
        public readonly Priority $priority = Priority::NORMAL,
    ) {
    }
}
