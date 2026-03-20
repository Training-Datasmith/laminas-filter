<?php

declare (strict_types=1);
namespace Laminas\Filter;

use const FILTER_VALIDATE_INT;
use function filter_var;
use function is_array;
use function is_numeric;
use function sprintf;
/**
 * @psalm-type Options = array{
 *     null_on_empty?: bool,
 *     null_on_all_empty?: bool,
 * }
 * @implements FilterInterface<string|null>
 */
final readonly class Month_Select implements Filter_Interface
{
    private bool $return_null_if_any_field_empty;
    private bool $return_null_if_all_fields_empty;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->return_null_if_any_field_empty = $options['null_on_empty'] ?? false;
        $this->return_null_if_all_fields_empty = $options['null_on_all_empty'] ?? false;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
    /**
     * Returns the result of filtering $value
     *
     * @template T
     * @param T $value
     * @return string|null|T
     */
    public function filter(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $month = $value['month'] ?? null;
        /** @var mixed $month */
        $month = $month === '' ? null : $month;
        $year = $value['year'] ?? null;
        /** @var mixed $year */
        $year = $year === '' ? null : $year;
        if ($this->return_null_if_any_field_empty && ($month === null || $year === null)) {
            return null;
        }
        if ($this->return_null_if_all_fields_empty && $month === null && $year === null) {
            return null;
        }
        if (!$this->is_parsable_as_date_value($month, 1, 12) || !$this->is_parsable_as_date_value($year, 0, 9999)) {
            /** @psalm-var T */
            return $value;
        }
        return sprintf('%d-%02d', $year, $month);
    }
    /** @psalm-assert-if-true int $value */
    private function is_parsable_as_date_value(mixed $value, int $lowest_value, int $highest_value): bool
    {
        if (!is_numeric($value) || filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $lowest_value, 'max_range' => $highest_value]]) === false) {
            return false;
        }
        return true;
    }
}