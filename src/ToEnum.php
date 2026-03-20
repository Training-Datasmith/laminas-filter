<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function assert;
use Backed_Enum;
use function constant;
use function defined;
use function is_a;
use function is_int;
use function is_string;
use Unit_Enum;
/**
 * @psalm-type Options = array{
 *     enum: class-string<UnitEnum>|class-string<BackedEnum>,
 * }
 * @implements FilterInterface<UnitEnum|BackedEnum>
 */
final readonly class To_Enum implements Filter_Interface
{
    /** @var class-string<UnitEnum>|class-string<BackedEnum> */
    private string $enum;
    /** @param Options $options */
    public function __construct(array $options)
    {
        $this->enum = $options['enum'];
    }
    public function filter(mixed $value): mixed
    {
        if (!is_int($value) && !is_string($value)) {
            return $value;
        }
        if (is_a($this->enum, Backed_Enum::class, true)) {
            $enum = null;
            $string_backed = is_string($this->enum::cases()[0]->value);
            if ($string_backed && is_string($value)) {
                $enum = $this->enum::try_from($value);
            }
            if (!$string_backed && (is_int($value) || (string) (int) $value === $value)) {
                $enum = $this->enum::try_from((int) $value);
            }
            if ($enum !== null) {
                return $enum;
            }
        }
        if (!is_string($value)) {
            return $value;
        }
        $constant_name = $this->enum . '::' . $value;
        if (defined($constant_name)) {
            /** @psalm-suppress MixedAssignment */
            $enum = constant($constant_name);
            assert($enum instanceof $this->enum);
            return $enum;
        }
        return $value;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}