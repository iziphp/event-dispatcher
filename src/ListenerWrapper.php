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

    /**
     * @param ContainerInterface $container
     * @param string|callable $listener
     * @param int $priority
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct(
        private ContainerInterface $container,
        string|callable $listener,
        private int $priority,
    ) {
        if (!is_callable($listener) && !method_exists($listener, "__invoke")) {
            throw new InvalidArgumentException("Listener must be a callable or a class with __invoke method");
        }

        $this->listener = $listener;
    }

    /**
     * Get listener priority
     *
     * @return int
     */
    public function getPriority(): int
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
        $this->resolve();
        return $this->listener;
    }

    /**
     * @return void
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function resolve(): void
    {
        if (!is_callable($this->listener)) {
            $this->listener = $this->container->get($this->listener);
        }
    }
}
