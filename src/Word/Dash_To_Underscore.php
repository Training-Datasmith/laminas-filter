<?php

declare (strict_types=1);
namespace Laminas\Filter\Word;

use Laminas\Filter\Filter_Interface;
/** @implements FilterInterface<string|array<array-key, string|mixed>> */
final class Dash_To_Underscore implements Filter_Interface
{
    public function filter(mixed $value): mixed
    {
        return (new Separator_To_Separator(['search_separator' => '-', 'replacement_separator' => '_']))->filter($value);
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}