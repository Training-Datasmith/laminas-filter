<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function array_change_key_case;
use function array_combine;
use function array_fill;
use function array_is_list;
use function array_map;
use function array_merge;
use const CASE_LOWER;
use function count;
use function in_array;
use function is_scalar;
use function preg_match;
use function preg_match_all;
use function sprintf;
use function str_contains;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;
/**
 * @psalm-type Options = array{
 *     allowTags?: list<string>|array<string, list<string>>,
 *     allowAttribs?: list<string>
 * }
 * @implements FilterInterface<string>
 */
final readonly class Strip_Tags implements Filter_Interface
{
    /**
     * Array of allowed tags and allowed attributes for each allowed tag
     *
     * Tags are stored in the array keys, and the array values are themselves
     * arrays of the attributes allowed for the corresponding tag.
     *
     * @var array<string, list<string>>
     */
    private array $tags_allowed;
    /**
     * Array of allowed attributes for all allowed tags
     *
     * Attributes stored here are allowed for all of the allowed tags.
     *
     * @var list<string>
     */
    private array $attributes_allowed;
    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->attributes_allowed = array_map(strtolower(...), $options['allowAttribs'] ?? []);
        $tags_allowed = $options['allowTags'] ?? [];
        if (array_is_list($tags_allowed)) {
            /** @psalm-var list<string> $tagsAllowed */
            $tags = array_map(strtolower(...), $tags_allowed);
            $this->tags_allowed = array_combine($tags, array_fill(0, count($tags), []));
            return;
        }
        /** @psalm-var array<string, list<string>> $tagsAllowed */
        $this->tags_allowed = array_map(static fn(array $attributes): array => array_map(strtolower(...), $attributes), array_change_key_case($tags_allowed, CASE_LOWER));
    }
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     */
    public function filter(mixed $value): mixed
    {
        if (!is_scalar($value)) {
            return $value;
        }
        $value = (string) $value;
        // Strip HTML comments first
        $open = '<!--';
        $open_len = strlen($open);
        $close = '-->';
        $close_len = strlen($close);
        while (($start = strpos($value, $open)) !== false) {
            $end = strpos($value, $close, $start + $open_len);
            if ($end === false) {
                $value = substr($value, 0, $start);
            } else {
                $value = substr($value, 0, $start) . substr($value, $end + $close_len);
            }
        }
        // Initialize accumulator for filtered data
        $data_filtered = '';
        // Parse the input data iteratively as regular pre-tag text followed by a
        // tag; either may be empty strings
        preg_match_all('/([^<]*)(<?[^>]*>?)/', $value, $matches);
        // Iterate over each set of matches
        foreach ($matches[1] as $index => $pre_tag) {
            // If the pre-tag text is non-empty, strip any ">" characters from it
            if (strlen($pre_tag)) {
                $pre_tag = str_replace('>', '', $pre_tag);
            }
            // If a tag exists in this match, then filter the tag
            $tag = $matches[2][$index];
            if (strlen($tag)) {
                $tag_filtered = $this->filter_tag($tag);
            } else {
                $tag_filtered = '';
            }
            // Add the filtered pre-tag text and filtered tag to the data buffer
            $data_filtered .= $pre_tag . $tag_filtered;
        }
        // Return the filtered data
        return $data_filtered;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
    /**
     * Filters a single tag against the current option settings
     */
    private function filter_tag(string $tag): string
    {
        // Parse the tag into:
        // 1. a starting delimiter (mandatory)
        // 2. a tag name (if available)
        // 3. a string of attributes (if available)
        // 4. an ending delimiter (if available)
        $is_match = preg_match('~(</?)(\w*)((/(?!>)|[^/>])*)(/?>)~', $tag, $matches);
        // If the tag does not match, then strip the tag entirely
        if (!$is_match) {
            return '';
        }
        // Save the matches to more meaningfully named variables
        $tag_start = $matches[1];
        $tag_name = strtolower($matches[2]);
        $tag_attributes = $matches[3];
        $tag_end = $matches[5];
        // If the tag is not an allowed tag, then remove the tag entirely
        if (!isset($this->tags_allowed[$tag_name])) {
            return '';
        }
        $allowed_attributes = array_merge($this->attributes_allowed, $this->tags_allowed[$tag_name]);
        // Trim the attribute string of whitespace at the ends
        $tag_attributes = trim($tag_attributes);
        // If there are non-whitespace characters in the attribute string
        if (strlen($tag_attributes)) {
            // Parse iteratively for well-formed attributes
            preg_match_all('/([\w-]+)\s*=\s*(?:(")(.*?)"|(\')(.*?)\')/s', $tag_attributes, $matches);
            // Initialize valid attribute accumulator
            $tag_attributes = '';
            // Iterate over each matched attribute
            foreach ($matches[1] as $index => $attribute_name) {
                $attribute_name = strtolower($attribute_name);
                $attribute_delimiter = $matches[2][$index] === '' ? $matches[4][$index] : $matches[2][$index];
                $attribute_value = $matches[3][$index] === '' ? $matches[5][$index] : $matches[3][$index];
                // If the attribute is not allowed, then remove it entirely
                if (!in_array($attribute_name, $allowed_attributes, true)) {
                    continue;
                }
                // Add the attribute to the accumulator
                $tag_attributes .= sprintf(' %s=%s%s%s', $attribute_name, $attribute_delimiter, $attribute_value, $attribute_delimiter);
            }
        }
        // Reconstruct tags ending with "/>" as backwards-compatible XHTML tag
        if (str_contains($tag_end, '/')) {
            $tag_end = " {$tag_end}";
        }
        // Return the filtered tag
        return $tag_start . $tag_name . $tag_attributes . $tag_end;
    }
}