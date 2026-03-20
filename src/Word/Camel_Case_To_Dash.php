<?php

declare (strict_types=1);
namespace Laminas\Filter\Word;

use Laminas\Filter\Filter_Interface;
/** @implements FilterInterface<string|array<array-key, string|mixed>> */
final class Camel_Case_To_Dash implements Filter_Interface
{
    public function filter(mixed $value): mixed
    {
        $filter = new Camel_Case_To_Separator(['separator' => '-']);
        return $filter->filter($value);
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}