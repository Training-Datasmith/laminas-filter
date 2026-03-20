<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function count;
use Countable;
use function is_array;
use function is_callable;
use function is_int;
use function is_string;
use IteratorAggregate;
use Laminas\Filter\Exception\Invalid_Specification_Array_Exception;
use Laminas\Stdlib\Priority_Queue;
use Psr\Container\Container_Exception_Interface;
use Traversable;
/**
 * @psalm-type InstanceType = FilterInterface|(callable(mixed): mixed)
 * @psalm-type FilterSpecification = array{
 *     name: string|class-string<FilterInterface>,
 *     options?: array<string, mixed>,
 *     priority?: int,
 * }|InstanceType
 * @psalm-type FilterChainConfiguration = array{
 *     filters?: list<FilterSpecification>,
 *     callbacks?: list<array{
 *         callback: FilterInterface|(callable(mixed): mixed),
 *         priority?: int,
 *     }>
 * }
 * @implements IteratorAggregate<array-key, InstanceType>
 * @implements FilterChainInterface<mixed>
 */
final class Filter_Chain implements Filter_Chain_Interface, Countable, IteratorAggregate
{
    /** @var PriorityQueue<InstanceType, int> */
    private Priority_Queue $filters;
    /**
     * @param FilterChainConfiguration $options
     * @throws ContainerExceptionInterface If any filter cannot be retrieved from the plugin manager.
     */
    public function __construct(private readonly Filter_Plugin_Manager $plugins, array $options = [])
    {
        /** @var PriorityQueue<InstanceType, int> $priorityQueue */
        $priority_queue = new Priority_Queue();
        $this->filters = $priority_queue;
        $callbacks = $options['callbacks'] ?? [];
        foreach ($callbacks as $spec) {
            $this->attach($spec['callback'], $spec['priority'] ?? self::DEFAULT_PRIORITY);
        }
        $filters = $options['filters'] ?? [];
        foreach ($filters as $spec) {
            if (is_callable($spec) || $spec instanceof Filter_Interface) {
                $this->attach($spec);
                continue;
            }
            $this->attach_by_name($spec['name'], $spec['options'] ?? [], $spec['priority'] ?? self::DEFAULT_PRIORITY);
        }
    }
    /** Return the count of attached filters */
    public function count(): int
    {
        return count($this->filters);
    }
    public function attach(Filter_Interface|callable $callback, int $priority = self::DEFAULT_PRIORITY): self
    {
        $this->filters->insert($callback, $priority);
        return $this;
    }
    public function attach_by_name(string $name, array $options = [], int $priority = self::DEFAULT_PRIORITY): self
    {
        if ($options === []) {
            /** @psalm-var FilterInterface $filter */
            $filter = $this->plugins->get($name);
        } else {
            /** @psalm-var FilterInterface $filter */
            $filter = $this->plugins->build($name, $options);
        }
        return $this->attach($filter, $priority);
    }
    /**
     * Merge the filter chain with the one given in parameter
     *
     * @return $this
     */
    public function merge(Filter_Chain $filter_chain): self
    {
        foreach ($filter_chain->filters->to_array(Priority_Queue::EXTR_BOTH) as $item) {
            $this->attach($item['data'], $item['priority']);
        }
        return $this;
    }
    public function filter(mixed $value): mixed
    {
        foreach ($this as $filter) {
            $value = $filter($value);
        }
        return $value;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
    /**
     * Prevent clones from mutating the composed priority queue
     */
    public function __clone()
    {
        $this->filters = clone $this->filters;
    }
    /** @return Traversable<array-key, FilterInterface|callable(mixed): mixed> */
    public function getIterator(): Traversable
    {
        return clone $this->filters;
    }
    /**
     * @psalm-assert FilterChainConfiguration $spec
     * @throws InvalidSpecificationArrayException If the specification is invalid.
     */
    public static function validate_specification(array $spec): void
    {
        /** @psalm-var mixed $filters */
        $filters = $spec['filters'] ?? null;
        /** @psalm-var mixed $callbacks */
        $callbacks = $spec['callbacks'] ?? null;
        if ($filters === null && $callbacks === null) {
            return;
            // An effectively empty specification is OK
        }
        if ($filters !== null) {
            self::validate_filter_list($filters);
        }
        if ($callbacks !== null) {
            self::validate_callback_list($callbacks);
        }
    }
    private static function validate_filter_list(mixed $spec): void
    {
        if (!is_array($spec)) {
            throw Invalid_Specification_Array_Exception::because_the_filter_list_must_be_an_array($spec);
        }
        /** @psalm-var mixed $item */
        foreach ($spec as $item) {
            self::validate_filter_specification($item);
        }
    }
    private static function validate_filter_specification(mixed $spec): void
    {
        if (is_callable($spec) || $spec instanceof Filter_Interface) {
            return;
        }
        if (!is_array($spec)) {
            throw Invalid_Specification_Array_Exception::because_filter_spec_must_be_an_array($spec);
        }
        $name = $spec['name'] ?? null;
        $options = $spec['options'] ?? null;
        $priority = $spec['priority'] ?? null;
        if (!is_string($name) || $name === '') {
            throw Invalid_Specification_Array_Exception::because_filter_names_must_be_a_string($name ?? null);
        }
        if ($options !== null && !is_array($options)) {
            throw Invalid_Specification_Array_Exception::because_options_should_be_arrays($options);
        }
        if ($priority !== null && !is_int($priority)) {
            throw Invalid_Specification_Array_Exception::because_filter_priority_must_be_an_integer($priority);
        }
    }
    private static function validate_callback_list(mixed $spec): void
    {
        if (!is_array($spec)) {
            throw Invalid_Specification_Array_Exception::because_callback_list_must_be_a_list($spec);
        }
        /** @psalm-var mixed $item */
        foreach ($spec as $item) {
            self::validate_callback($item);
        }
    }
    private static function validate_callback(mixed $spec): void
    {
        if (!is_array($spec)) {
            throw Invalid_Specification_Array_Exception::because_callback_must_be_array($spec);
        }
        $callback = $spec['callback'] ?? null;
        $priority = $spec['priority'] ?? null;
        if (!is_callable($callback)) {
            throw Invalid_Specification_Array_Exception::because_callback_must_be_present($callback);
        }
        if ($priority !== null && !is_int($priority)) {
            throw Invalid_Specification_Array_Exception::because_filter_priority_must_be_an_integer($priority);
        }
    }
}