<?php

namespace PhpStandard\EventDispatcher\Mapper;

use PhpStandard\EventDispatcher\Attributes\Listener;
use PhpStandard\EventDispatcher\EventMapperInterface;
use PhpStandard\EventDispatcher\ListenerWrapper;
use Psr\Container\ContainerInterface;
use ReflectionObject;

class EventAttributeMapper implements EventMapperInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    /** @inheritDoc */
    public function getListenersForEvent(object $event): iterable
    {
        $reflection = new ReflectionObject($event);

        while ($reflection) {
            $attributes = $reflection->getAttributes(Listener::class);

            foreach ($attributes as $attribute) {
                $listener = $attribute->newInstance();

                yield new ListenerWrapper(
                    $this->container,
                    $listener->className,
                    $listener->priority
                );
            }

            $reflection = $reflection->getParentClass();
        }
    }
}
