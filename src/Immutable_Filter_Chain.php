<?php

declare (strict_types=1);
namespace Laminas\Filter;

use Laminas\Stdlib\Priority_Queue;
use Psr\Container\Container_Exception_Interface;
/**
 * @psalm-type InstanceType = FilterInterface|(callable(mixed): mixed)
 * @psalm-type ChainSpec = array{
 *     filters?: list<array{
 *         name: string|class-string<FilterInterface>,
 *         options?: array<string, mixed>,
 *         priority?: int|null,
 *     }>,
 *     callbacks?: list<array{
 *         callback: FilterInterface|(callable(mixed): mixed),
 *         priority?: int|null,
 *     }>,
 * }
 * @implements FilterChainInterface<mixed>
 */
final readonly class Immutable_Filter_Chain implements Filter_Chain_Interface
{
    /** @var PriorityQueue<InstanceType, int> */
    private Priority_Queue $filters;
    /** @param PriorityQueue<InstanceType, int>|null $filters */
    private function __construct(private Filter_Plugin_Manager $plugin_manager, Priority_Queue|null $filters)
    {
        /** @var PriorityQueue<InstanceType, int> $default */
        $default = new Priority_Queue();
        $this->filters = $filters ?? $default;
    }
    public function filter(mixed $value): mixed
    {
        foreach ($this->filters as $filter) {
            /** @var mixed $value */
            $value = $filter($value);
        }
        return $value;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
    public static function empty(Filter_Plugin_Manager $plugin_manager): self
    {
        return new self($plugin_manager, null);
    }
    /**
     * Construct a filter chain from a specification
     *
     * @param ChainSpec $spec
     * @throws ContainerExceptionInterface If any named filters cannot be retrieved from the plugin manager.
     */
    public static function from_array(array $spec, Filter_Plugin_Manager $plugin_manager): self
    {
        /** @psalm-var PriorityQueue<InstanceType, int> $queue */
        $queue = new Priority_Queue();
        $chain = new self($plugin_manager, $queue);
        $callables = $spec['callbacks'] ?? [];
        foreach ($callables as $set) {
            $chain = $chain->attach($set['callback'], $set['priority'] ?? self::DEFAULT_PRIORITY);
        }
        $filters = $spec['filters'] ?? [];
        foreach ($filters as $filter) {
            $chain = $chain->attach_by_name($filter['name'], $filter['options'] ?? [], $filter['priority'] ?? self::DEFAULT_PRIORITY);
        }
        return $chain;
    }
    public function attach(Filter_Interface|callable $callback, int $priority = self::DEFAULT_PRIORITY): self
    {
        $filters = clone $this->filters;
        $filters->insert($callback, $priority);
        return new self($this->plugin_manager, $filters);
    }
    public function attach_by_name(string $name, array $options = [], int $priority = self::DEFAULT_PRIORITY): self
    {
        if ($options === []) {
            /** @psalm-var FilterInterface $filter */
            $filter = $this->plugin_manager->get($name);
        } else {
            /** @psalm-var FilterInterface $filter */
            $filter = $this->plugin_manager->build($name, $options);
        }
        $filters = clone $this->filters;
        $filters->insert($filter, $priority);
        return new self($this->plugin_manager, $filters);
    }
}