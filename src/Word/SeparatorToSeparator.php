<?php

declare (strict_types=1);
namespace Laminas\Filter\Word;

use function assert;
use function is_string;
use Laminas\Filter\Filter_Interface;
use Laminas\Filter\Scalar_Or_Array_Filter_Callback;
use function preg_quote;
use function preg_replace;
/**
 * @psalm-type Options = array{
 *     search_separator?: string,
 *     replacement_separator?: string,
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final readonly class Separator_To_Separator implements Filter_Interface
{
    private string $search_separator;
    private string $replacement_separator;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->search_separator = $options['search_separator'] ?? ' ';
        $this->replacement_separator = $options['replacement_separator'] ?? '-';
    }
    public function filter(mixed $value): mixed
    {
        return Scalar_Or_Array_Filter_Callback::apply_recursively($value, function (string $input): string {
            $result = preg_replace('#' . preg_quote($this->search_separator, '#') . '#', $this->replacement_separator, $input);
            assert(is_string($result));
            return $result;
        });
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}