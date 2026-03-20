<?php

declare (strict_types=1);
namespace Laminas\Filter\Word;

use Laminas\Filter\Filter_Interface;
/**
 * @psalm-type Options = array{
 *     separator?: string,
 * }
 * @template TOptions of Options
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final readonly class Separator_To_Dash implements Filter_Interface
{
    private string $separator;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->separator = $options['separator'] ?? ' ';
    }
    public function filter(mixed $value): mixed
    {
        return (new Separator_To_Separator(['search_separator' => $this->separator, 'replacement_separator' => '-']))->filter($value);
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}