<?php

namespace PhpStandard\EventDispatcher;

enum Priority: int
{
    /**
     * Listener priority values
     * Listeners with higher priority number will be called first.
     */
    case LOW = 0;
    case NORMAL = 50;
    case HIGH = 100;
}
