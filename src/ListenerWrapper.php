<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/** @package PhpStandard\EventDispatcher */
class ListenerWrapper
{
    /**
     * Property type declarations support all type declarations supported by
     * PHP, with the exception of void and callable.
     *
     * @see https://wiki.php.net/rfc/typed_properties_v2
     * @see https://wiki.php.net/rfc/consistent_callables
     */

    /** @var string|callable $listener */
    private $listener;

    public function __construct(
        private ContainerInterface $container,
        string|callable $listener,
        private Priority $priority,
    ) {
        if (!is_callable($listener) && !method_exists($listener, "__invoke")) {
            throw new InvalidArgumentException("Listener must be a callable or a class with __invoke method");
        }

        $this->listener = $listener;
    }

    public function getPriority(): Priority
    {
        return $this->priority;
    }

    /**
     * Get listener callback
     *
     * @return callable
     */
    public function getListener(): callable
    {
        return $this->resolve();
    }

    /**
     * @return callable
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function resolve(): callable
    {
        if (!is_callable($this->listener)) {
            /** @var callable $listener */
            $listener = $this->container->get($this->listener);
            $this->listener = $listener;
        }

        return $this->listener;
    }
}
