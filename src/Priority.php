<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher;

/**
 * The priority levels for event listeners.
 *
 * @package PhpStandard\EventDispatcher
 */
enum Priority: int
{
    /**
     * Low priority.
     */
    case LOW = 0;

    /**
     * Normal priority.
     */
    case NORMAL = 50;

    /**
     * High priority.
     */
    case HIGH = 100;
}
