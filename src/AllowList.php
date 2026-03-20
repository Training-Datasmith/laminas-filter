<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function array_values;
use function in_array;
use function iterator_to_array;
use Traversable;
/**
 * @psalm-type Options = array{
 *     strict?: bool,
 *     list?: iterable<array-key, mixed>,
 * }
 * @implements FilterInterface<null>
 */
final readonly class Allow_List implements Filter_Interface
{
    private bool $strict;
    /** @var list<mixed> */
    private array $list;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->strict = $options['strict'] ?? true;
        $list = $options['list'] ?? [];
        $this->list = $list instanceof Traversable ? iterator_to_array($list, false) : array_values($list);
    }
    /**
     * {@inheritDoc}
     *
     * Will return $value if its present in the allow-list. If $value is rejected then it will return null.
     */
    public function filter(mixed $value): mixed
    {
        return in_array($value, $this->list, $this->strict) ? $value : null;
    }
    /**
     * {@inheritDoc}
     *
     * Will return $value if its present in the allow-list. If $value is rejected then it will return null.
     */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}