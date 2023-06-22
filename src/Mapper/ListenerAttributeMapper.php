<?php

namespace PhpStandard\EventDispatcher\Mapper;

use PhpStandard\EventDispatcher\Attributes\Subscribe;
use PhpStandard\EventDispatcher\EventMapperInterface;
use PhpStandard\EventDispatcher\ListenerWrapper;
use PhpStandard\EventDispatcher\Priority; // Used in dosctype
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

/** @package PhpStandard\EventDispatcher\Mapper */
class ListenerAttributeMapper implements EventMapperInterface
{
    /** @var array<string> $paths */
    private array $paths = [];
    private bool $isCachingEnabled = false;

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
     * @param null|CacheItemPoolInterface $cache
     * @return void
     */
    public function __construct(
        private ContainerInterface $container,
        private ?CacheItemPoolInterface $cache = null
    ) {
    }

    /** @inheritDoc */
    public function getListenersForEvent(object $event): iterable
    {
        if (!$this->isSubsResolved) {
            $this->lookupForSubscribers();
        }

        foreach ($this->subscribers as $subscriber) {
            yield new ListenerWrapper(
                $this->container,
                $subscriber['listener'],
                $subscriber['priority']
            );
        }
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
     * @return ListenerAttributeMapper
     */
    public function addPath(string $path): self
    {
        $this->paths[] = $path;
        return $this;
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
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

    /**
     * @param string $path
     * @return void
     */
    private function lookupForSubscribersInPath(string $path): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        $includedFiles = [];
        $declared = get_declared_classes();

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            require_once $file->getRealPath();
            $includedFiles[] = $file->getRealPath();
        }

        foreach ($declared as $className) {
            $reflection = new ReflectionClass($className);
            $sourceFile = $reflection->getFileName();

            if (!in_array($sourceFile, $includedFiles, true)) {
                continue;
            }

            $this->lookupForSubscribersInFile($reflection);
        }
    }

    /**
     * @param SplFileInfo $file
     * @return void
     */
    private function lookupForSubscribersInFile(ReflectionClass $reflection): void
    {
        while ($reflection) {
            if (!$reflection->isInstantiable()) {
                break;
            }

            $attributes = $reflection->getAttributes(Subscribe::class);

            foreach ($attributes as $attribute) {
                $subscriber = $attribute->newInstance();
                $this->subscribers[] = [
                    'eventType' => $subscriber->eventType,
                    'listener' => $reflection->getName(),
                    'priority' => $subscriber->priority,
                ];
            }

            $reflection = $reflection->getParentClass();
        }
    }
}
