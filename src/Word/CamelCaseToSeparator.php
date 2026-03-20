<?php

declare (strict_types=1);
namespace Laminas\Filter\Word;

use function assert;
use function implode;
use function is_array;
use Laminas\Filter\Filter_Interface;
use Laminas\Filter\Scalar_Or_Array_Filter_Callback;
use function preg_split;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;
/**
 * @psalm-type Options = array{
 *     separator?: string,
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final readonly class Camel_Case_To_Separator implements Filter_Interface
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
        return Scalar_Or_Array_Filter_Callback::apply_recursively($value, function (string $input): string {
            $pattern = <<<REGEXP
            /
            (
                (?:\\p{Lu}\\p{Ll}+) # Upper followed by lower
                |
                (?:\\p{Lu}+(?!\\p{Ll})) # Upper not followed by lower
                |
                (?:\\p{N}+) # Runs of numbers
            )
            /ux
            REGEXP;
            $parts = preg_split($pattern, $input, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            assert(is_array($parts));
            return implode($this->separator, $parts);
        });
    }
}