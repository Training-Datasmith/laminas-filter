<?php

declare (strict_types=1);
namespace Laminas\Filter;

/** @implements FilterInterface<mixed> */
final class To_String implements Filter_Interface
{
    /**
     * Returns (string) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     */
    public function filter(mixed $value): mixed
    {
        return Scalar_Or_Array_Filter_Callback::apply_recursively($value, fn(string $value): string => $value);
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}