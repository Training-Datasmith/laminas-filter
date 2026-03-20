<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function array_map;
use function in_array;
use Laminas\Filter\Exception\InvalidArgumentException;
use function mb_internal_encoding;
use function mb_list_encodings;
use function sprintf;
use function strtolower;
/** @internal */
final class Encoding_Option
{
    /**
     * Asserts the given string is an encoding supported by ext-mbstring, returning the encoding when valid
     */
    public static function assert(string $encoding): string
    {
        $encoding = strtolower($encoding);
        $available = array_map(strtolower(...), mb_list_encodings());
        if (!in_array($encoding, $available, true)) {
            throw new InvalidArgumentException(sprintf("Encoding '%s' is not supported by the mbstring extension", $encoding));
        }
        return $encoding;
    }
    /**
     * Asserts $encoding is a supported encoding with a fallback when encoding is unspecified
     */
    public static function assert_with_default(string|null $encoding): string
    {
        $encoding ??= mb_internal_encoding();
        return self::assert($encoding);
    }
}