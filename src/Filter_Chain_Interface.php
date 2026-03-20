<?php

declare (strict_types=1);
namespace Laminas\Filter;

use Psr\Container\Container_Exception_Interface;
/**
 * @template TFilteredValue
 * @extends FilterInterface<TFilteredValue>
 * @psalm-type InstanceType = FilterInterface|(callable(mixed): mixed)
 */
interface Filter_Chain_Interface extends Filter_Interface
{
    public const DEFAULT_PRIORITY = 1000;
    /**
     * Attach a filter to the chain
     *
     * @param InstanceType $callback A Filter implementation or valid PHP callback
     * @param int $priority Priority at which to enqueue filter; defaults to 1000 (higher executes earlier)
     */
    public function attach(Filter_Interface|callable $callback, int $priority = self::DEFAULT_PRIORITY): self;
    /**
     * Attach a filter to the chain using an alias or FQCN
     *
     * Retrieves the filter from the composed plugin manager, and then calls attach()
     * with the retrieved instance.
     *
     * @param class-string<FilterInterface>|string $name
     * @param array<string, mixed> $options Construction options for the desired filter
     * @param int $priority Priority at which to enqueue filter; defaults to 1000 (higher executes earlier)
     * @throws ContainerExceptionInterface If the filter cannot be retrieved from the plugin manager.
     */
    public function attach_by_name(string $name, array $options = [], int $priority = self::DEFAULT_PRIORITY): self;
}