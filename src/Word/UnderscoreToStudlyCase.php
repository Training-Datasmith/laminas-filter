<?php

declare (strict_types=1);
namespace Laminas\Filter\Word;

use function is_array;
use function is_scalar;
use Laminas\Filter\Filter_Interface;
use Laminas\Filter\Scalar_Or_Array_Filter_Callback;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;
/** @implements FilterInterface<string|array<array-key, string|mixed>> */
final class Underscore_To_Studly_Case implements Filter_Interface
{
    public function filter(mixed $value): mixed
    {
        if (!is_scalar($value) && !is_array($value)) {
            return $value;
        }
        /** @var string|array $value */
        $value = (new Separator_To_Camel_Case(['separator' => '_']))->filter($value);
        return Scalar_Or_Array_Filter_Callback::apply_recursively($value, function (string $input): string {
            if (0 === mb_strlen($input)) {
                return $input;
            }
            return mb_strtolower(mb_substr($input, 0, 1)) . mb_substr($input, 1);
        });
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}