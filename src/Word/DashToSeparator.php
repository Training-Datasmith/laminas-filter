<?php

declare (strict_types=1);
namespace Laminas\Filter\Word;

use Laminas\Filter\Filter_Interface;
use Laminas\Filter\Scalar_Or_Array_Filter_Callback;
use function str_replace;
/**
 * @psalm-type Options = array{
 *     separator?: string,
 *     ...
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final readonly class Dash_To_Separator implements Filter_Interface
{
    private string $separator;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->separator = $options['separator'] ?? ' ';
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
    public function filter(mixed $value): mixed
    {
        return Scalar_Or_Array_Filter_Callback::apply_recursively($value, fn(string $input): string => str_replace('-', $this->separator, $input));
    }
}