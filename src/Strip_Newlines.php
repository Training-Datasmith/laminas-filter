<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function str_replace;
/** @implements FilterInterface<string|array<array-key, string|mixed>> */
final class Strip_Newlines implements Filter_Interface
{
    /**
     * Returns $value without newline control characters
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        return Scalar_Or_Array_Filter_Callback::apply_recursively($value, static fn(string $value): string => str_replace(["\n", "\r"], '', $value));
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}