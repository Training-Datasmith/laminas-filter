<?php

declare (strict_types=1);
namespace Laminas\Filter\Exception;

use function get_debug_type;
use InvalidArgumentException;
use function sprintf;
final class Invalid_Specification_Array_Exception extends InvalidArgumentException implements Exception_Interface
{
    public static function because_the_filter_list_must_be_an_array(mixed $spec): self
    {
        return new self(sprintf('The `filters` key must be a list of arrays or filter instances. Received %s', get_debug_type($spec)));
    }
    public static function because_filter_spec_must_be_an_array(mixed $spec): self
    {
        return new self(sprintf('Each member of the `filters` list must be array specification. Received %s', get_debug_type($spec)));
    }
    public static function because_filter_names_must_be_a_string(mixed $name): self
    {
        return new self(sprintf('Individual filter array specifications must have the key `name` that references a filter by its ' . 'fully qualified class name or an alias configured in the plugin manager. Received %s', get_debug_type($name)));
    }
    public static function because_options_should_be_arrays(mixed $options): self
    {
        return new self(sprintf('Filter options must be an array when specified. Received %s', get_debug_type($options)));
    }
    public static function because_filter_priority_must_be_an_integer(mixed $priority): self
    {
        return new self(sprintf('Filter priorities must be integers when specified. Received %s', get_debug_type($priority)));
    }
    public static function because_callback_list_must_be_a_list(mixed $spec): self
    {
        return new self(sprintf('The `callbacks` key must be a list of arrays. Received %s', get_debug_type($spec)));
    }
    public static function because_callback_must_be_array(mixed $spec): self
    {
        return new self(sprintf('All items listed under the `callbacks` key must be arrays. Received %s', get_debug_type($spec)));
    }
    public static function because_callback_must_be_present(mixed $callback): self
    {
        return new self(sprintf('Each callback filter listed under `callbacks` must contain a callable under the key ' . '`callback`. Received %s', get_debug_type($callback)));
    }
}