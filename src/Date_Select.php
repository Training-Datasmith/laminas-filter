<?php

declare (strict_types=1);
namespace Laminas\Filter;

use DateTime;
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
final readonly class Date_Select implements Filter_Interface
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
        $day = $this->get_value($value, 'day');
        $month = $this->get_value($value, 'month');
        $year = $this->get_value($value, 'year');
        if ($this->return_null_if_any_field_empty && ($day === null || $month === null || $year === null)) {
            return null;
        }
        if ($this->return_null_if_all_fields_empty && $day === null && $month === null && $year === null) {
            return null;
        }
        if ($day === null || $month === null || $year === null) {
            return $value;
        }
        if (!$this->is_parsable_as_date_value($day, $month, $year)) {
            /** @psalm-var T */
            return $value;
        }
        return sprintf('%d-%02d-%02d', $year, $month, $day);
    }
    /**
     * @psalm-assert-if-true int $day
     * @psalm-assert-if-true int $month
     * @psalm-assert-if-true int $year
     */
    private function is_parsable_as_date_value(mixed $day, mixed $month, mixed $year): bool
    {
        if (!is_numeric($day) || !is_numeric($month) || !is_numeric($year)) {
            return false;
        }
        $date = DateTime::create_from_format('Y-m-d', $year . '-' . $month . '-' . $day);
        if (!$date || $date->format('Ymd') !== sprintf('%d%02d%02d', $year, $month, $day)) {
            return false;
        }
        return true;
    }
    /** @param mixed[] $value */
    private function get_value(array $value, string $string): mixed
    {
        $result = $value[$string] ?? null;
        return $result === '' ? null : $result;
    }
}