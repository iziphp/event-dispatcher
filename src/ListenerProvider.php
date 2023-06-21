<?php

declare(strict_types=1);

namespace PhpStandard\EventDispatcher;

use PhpStandard\EventDispatcher\Attributes\Listener;
use PhpStandard\EventDispatcher\Attributes\Subscribe;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionObject;
use SplFileInfo;

/** @package PhpStandard\EventDispatcher */
class ListenerProvider implements ListenerProviderInterface
{
    /**
     * An associative array of the listener wrappers
     * Key is the type of the event, value is the array of the ListenerWrapper.
     *
     * @var array<string,array<ListenerWrapper>>
     */
    private array $wrappers = [];

    /** @var array<string> $paths */
    private array $paths = [];
    private bool $isCachingEnabled = false;

    /** @var array<class-string,ListenerWrapper[]> */
    private array $resolved = [];

    /**
     * @var array<array{
     *  eventType: class-string,
     *  listener: string|callable,
     *  priority: Priority
     * }> The subscribers found during lookup.
     */
    private array $subscribers = [];
    private bool $isSubsResolved = false;

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function __construct(
        private ContainerInterface $container,
        private ?CacheItemPoolInterface $cache = null
    ) {
    }

    /**
     * @inheritDoc
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        yield from $this->getResolvedListeners($event);
    }

    /** @return void */
    public function enableCaching(): void
    {
        $this->isCachingEnabled = true;
    }

    /** @return void */
    public function disableCaching(): void
    {
        $this->isCachingEnabled = false;
    }

    /**
     * @param string $path
     * @return ListenerProvider
     */
    public function addPath(string $path): self
    {
        $this->paths[] = $path;
        return $this;
    }

    /**
     * @param string $eventType
     * @param string|callable $listener
     * @param Priority $priority
     * @return ListenerProvider
     */
    public function addEventListener(
        string $eventType,
        string|callable $listener,
        Priority $priority = Priority::NORMAL
    ): ListenerProvider {
        if (!isset($this->wrappers[$eventType])) {
            $this->wrappers[$eventType] = [];
        }

        $this->wrappers[$eventType][] = new ListenerWrapper(
            $this->container,
            $listener,
            $priority
        );

        return $this;
    }

    /**
     * Resolve the listeners for event type and
     * return resolved listeners iterable
     *
     * @param object $event
     * @return iterable<callable>
     */
    private function getResolvedListeners(object $event): iterable
    {
        if (!$this->isSubsResolved) {
            $this->lookupForSubscribers();
            $this->resolveSubscribers();
            $this->isSubsResolved = true;
        }

        foreach ($this->getWrappers($event) as $wrapper) {
            $listener = $wrapper->getListener();
            yield $listener;
        }
    }

    /**
     * Get original unresolved listener handles match for the event type
     *
     * @param object $event
     * @return iterable<ListenerWrapper>
     */
    private function getWrappers(object $event): iterable
    {
        if (!array_key_exists($event::class, $this->resolved)) {
            $this->findListenersByEventAttributes($event);

            $allWrappers = [];
            foreach ($this->wrappers as $eventType => $wrappers) {
                if (!$event instanceof $eventType) {
                    continue;
                }

                $allWrappers = array_merge($allWrappers, $wrappers);
            }

            $allWrappers = $this->sortWrappers(...$allWrappers);
            $this->resolved[$event::class] = $allWrappers;
        }

        yield from $this->resolved[$event::class];
    }

    /**
     * Sort listener wrappers by descending order priority
     *
     * @param ListenerWrapper $wrappers
     * @return ListenerWrapper[]
     */
    private function sortWrappers(ListenerWrapper ...$wrappers): array
    {
        usort(
            $wrappers,
            function (ListenerWrapper $left, ListenerWrapper $right) {
                return $right->getPriority()->value <=> $left->getPriority()->value;
            }
        );

        return $wrappers;
    }

    private function findListenersByEventAttributes(object $event): void
    {
        $reflection = new ReflectionObject($event);

        while ($reflection) {
            $attributes = $reflection->getAttributes(Listener::class);

            foreach ($attributes as $attribute) {
                $listener = $attribute->newInstance();
                $this->addEventListener(
                    $event::class,
                    $listener->className,
                    $listener->priority
                );
            }

            $reflection = $reflection->getParentClass();
        }
    }

    private function lookupForSubscribers(): void
    {
        if ($this->cache && $this->isCachingEnabled) {
            $item = $this->cache->getItem('subscribers');

            if ($item->isHit()) {
                /**
                 * @var array<array{
                 *  eventType: class-string,
                 *  listener: string|callable,
                 *  priority: Priority
                 * }> $subscribers
                 */
                $subscribers = $item->get();
                $this->subscribers = $subscribers;
                return;
            }
        }

        foreach ($this->paths as $path) {
            $this->lookupForSubscribersInPath($path);
        }
    }

    private function resolveSubscribers(): void
    {
        foreach ($this->subscribers as $subscriber) {
            $this->addEventListener(
                $subscriber['eventType'],
                $subscriber['listener'],
                $subscriber['priority']
            );
        }
    }

    private function lookupForSubscribersInPath(string $path): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $this->lookupForSubscribersInFile($file);
        }
    }

    private function lookupForSubscribersInFile(SplFileInfo $file): void
    {
        $tokens = token_get_all(file_get_contents($file->getPathname()) ?: '');

        foreach ($tokens as $token) {
            if (!is_array($token)) {
                continue;
            }

            if ($token[0] !== T_CLASS) {
                continue;
            }

            /** @var class-string $className */
            $className = $token[1];
            $reflection = new ReflectionClass($className);

            if (!$reflection->isInstantiable()) {
                continue;
            }

            $attributes = $reflection->getAttributes(Subscribe::class);

            foreach ($attributes as $attribute) {
                $subscriber = $attribute->newInstance();
                $this->subscribers[] = [
                    'eventType' => $subscriber->eventType,
                    'listener' => $className,
                    'priority' => $subscriber->priority,
                ];
            }
        }
    }
}
