<?php

namespace PhpStandard\EventDispatcher\Attributes;

use Attribute;
use PhpStandard\EventDispatcher\Priority;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Listener
{
    /**
     * @param class-string $className FQCN of invokable listener class
     * @param Priority $priority
     * @return void
     */
    public function __construct(
        public readonly string $className,
        public readonly Priority $priority = Priority::NORMAL,
    ) {
    }
}
