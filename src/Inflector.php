<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function array_keys;
use function array_map;
use function array_values;
use function assert;
use function get_object_vars;
use function is_array;
use function is_object;
use function is_scalar;
use function is_string;
use Laminas\Filter\Exception\InvalidArgumentException;
use function ltrim;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function str_replace;
use function str_starts_with;
/**
 * Filter chain for string inflection
 *
 * @psalm-import-type InstanceType from FilterPluginManager
 * @psalm-type RulesArray = array<string, string|list<string|InstanceType>>
 * @psalm-type Options = array{
 *     target: string,
 *     rules?: RulesArray,
 *     throwTargetExceptionsOn?: bool,
 *     targetReplacementIdentifier?: non-empty-string,
 * }
 * @implements FilterInterface<string>
 */
final readonly class Inflector implements Filter_Interface
{
    /** @var non-empty-string */
    private string $target;
    private bool $throw_target_exceptions_on;
    /** @var non-empty-string */
    private string $target_replacement_identifier;
    /** @var array<string, string|list<InstanceType>> */
    private array $rules;
    /** @param Options $options */
    public function __construct(private Filter_Plugin_Manager $plugin_manager, array $options)
    {
        $target = $options['target'] ?? null;
        if (!is_string($target) || $target === '') {
            throw new InvalidArgumentException('Inflector requires the target option to be a non-empty string');
        }
        $this->target = $target;
        $this->throw_target_exceptions_on = $options['throwTargetExceptionsOn'] ?? true;
        $this->target_replacement_identifier = $options['targetReplacementIdentifier'] ?? ':';
        $this->rules = $this->resolve_rules($options['rules'] ?? []);
    }
    /**
     * Resolve rules argument
     *
     * If prefixed with a ":" (colon), a filter rule will be added.
     * If not prefixed, a static string replacement will be added.
     *
     * example:
     * [
     *     ':controller' => [CamelCaseToUnderscore::class, StringToLower::class],
     *     ':action'     => [CamelCaseToUnderscore::class, StringToLower::class],
     *     'suffix'      => 'phtml',
     * ]
     *
     * @param RulesArray $rules
     * @return array<string, string|list<InstanceType>>
     */
    private function resolve_rules(array $rules): array
    {
        $resolved = [];
        foreach ($rules as $spec => $rule_set) {
            $name = ltrim($spec, ':');
            if (str_starts_with($spec, ':')) {
                $resolved[$name] = array_map(fn(string|Filter_Interface|callable $filter): Filter_Interface|callable => $this->load_filter($filter), is_string($rule_set) ? [$rule_set] : $rule_set);
            } else {
                assert(is_string($rule_set));
                $resolved[$name] = $rule_set;
            }
        }
        return $resolved;
    }
    public function filter(mixed $value): mixed
    {
        if (is_object($value)) {
            $value = get_object_vars($value);
        }
        if (!is_array($value)) {
            return $value;
        }
        // clean source
        $subject = [];
        foreach ($value as $source_name => $source_value) {
            if (!is_string($source_name)) {
                continue;
            }
            if (!is_scalar($source_value)) {
                continue;
            }
            $source_name = ltrim($source_name, ':');
            $subject[$source_name] = (string) $source_value;
        }
        $preg_quoted_target_replacement_identifier = preg_quote($this->target_replacement_identifier, '#');
        $processed_parts = [];
        foreach ($this->rules as $rule_name => $rule_value) {
            if (isset($subject[$rule_name])) {
                if (is_string($rule_value)) {
                    $processed_parts['#' . $preg_quoted_target_replacement_identifier . $rule_name . '#'] = str_replace('\\', '\\\\', $subject[$rule_name]);
                } else {
                    $processed_part = $subject[$rule_name];
                    foreach ($rule_value as $rule_filter) {
                        $processed_part = (string) $rule_filter($processed_part);
                    }
                    $processed_parts['#' . $preg_quoted_target_replacement_identifier . $rule_name . '#'] = str_replace('\\', '\\\\', $processed_part);
                }
            } elseif (is_string($rule_value)) {
                $processed_parts['#' . $preg_quoted_target_replacement_identifier . $rule_name . '#'] = str_replace('\\', '\\\\', $rule_value);
            }
        }
        // all of the values of processedParts would have been str_replace('\\', '\\\\', ..)'d
        // to disable preg_replace backreferences
        $inflected_target = preg_replace(array_keys($processed_parts), array_values($processed_parts), $this->target);
        assert($inflected_target !== null);
        if ($this->throw_target_exceptions_on && preg_match('#(?=' . $preg_quoted_target_replacement_identifier . '[A-Za-z]{1})#', $inflected_target)) {
            throw new Exception\RuntimeException('A replacement identifier ' . $this->target_replacement_identifier . ' was found inside the inflected target, perhaps a rule was not satisfied with a target source?  ' . 'Unsatisfied inflected target: ' . $inflected_target);
        }
        return $inflected_target;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
    /**
     * Resolve named filters and convert them to filter objects.
     *
     * @return InstanceType
     */
    private function load_filter(string|Filter_Interface|callable $rule): Filter_Interface|callable
    {
        if (!is_string($rule)) {
            return $rule;
        }
        return $this->plugin_manager->get($rule);
    }
}