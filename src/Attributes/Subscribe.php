<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher\Attributes;

use Attribute;
use PhpStandard\EventDispatcher\Priority;

/**
 * Marks a class as a subscriber for events. This attribute is intended to be
 * used in listener classes.
 *
 * Example usage:
 *
 * #[Subscribe(ExampleEvent::class, Priority::NORMAL)]
 * class ExampleEventListener {}
 *
 * @package PhpStandard\EventDispatcher\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Subscribe
{
    /**
     * Subscribe constructor.
     *
     * @param class-string $eventType FQCN of the event class
     * @param Priority $priority
     * The priority of the subscriber (optional, defaults to Priority::NORMAL)
     * @return void
     */
    public function __construct(
        public readonly string $eventType,
        public readonly Priority $priority = Priority::NORMAL,
    ) {
    }
}
