<?php

declare (strict_types=1);
namespace Laminas\Filter\File;

use function array_is_list;
use function file_exists;
use function fnmatch;
use function is_dir;
use function is_string;
use function is_writable;
use Laminas\Filter\Exception;
use Laminas\Filter\Filter_Interface;
use function pathinfo;
use function rename;
use function sprintf;
use function uniqid;
use function unlink;
/**
 * @psalm-type OptionsSet = array{
 *     match?: non-empty-string,
 *     target_directory?: non-empty-string,
 *     rename_to?: string,
 *     overwrite?: bool,
 *     randomize?: bool
 * }
 * @psalm-type Options = OptionsSet|list<OptionsSet>
 * @psalm-type DefaultedOptionsSet = array{
 *      match: non-empty-string,
 *      target_directory: non-empty-string,
 *      rename_to: string,
 *      overwrite: bool,
 *      randomize: bool
 *  }
 * @implements FilterInterface<string>
 */
final readonly class Rename implements Filter_Interface
{
    /** @var list<DefaultedOptionsSet> */
    private array $options;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $defaulted_options = [];
        if (array_is_list($options)) {
            /** @psalm-var OptionsSet $option */
            foreach ($options as $option) {
                $defaulted_options[] = $this->validate_and_default_options($option);
            }
        } else {
            /** @psalm-var OptionsSet $options */
            $defaulted_options[] = $this->validate_and_default_options($options);
        }
        $this->options = $defaulted_options;
    }
    /**
     * @param OptionsSet $options
     * @return DefaultedOptionsSet
     */
    private function validate_and_default_options(array $options): array
    {
        $target = $options['target_directory'] ?? '*';
        if ($target !== '*') {
            if (!is_dir($target)) {
                throw new Exception\InvalidArgumentException(sprintf('The target directory "%s" does not exist', $target));
            }
            if (!is_writable($target)) {
                throw new Exception\InvalidArgumentException(sprintf('The target directory "%s" is not writable', $target));
            }
        }
        return ['match' => $options['match'] ?? '*', 'target_directory' => $target, 'rename_to' => $options['rename_to'] ?? '*', 'overwrite' => $options['overwrite'] ?? false, 'randomize' => $options['randomize'] ?? false];
    }
    /**
     * @param DefaultedOptionsSet $matchingOptions
     * @throws Exception\InvalidArgumentException If the target file already exists.
     */
    private function rename_file(string $source_file_path, array $matching_options): string
    {
        $file = $this->get_file_name($source_file_path, $matching_options);
        if ($file === $source_file_path) {
            return $file;
        }
        if ($matching_options['overwrite'] && file_exists($file)) {
            unlink($file);
        }
        if (file_exists($file)) {
            throw new Exception\InvalidArgumentException(sprintf('"File "%s" could not be renamed to "%s"; target file already exists', $source_file_path, $file));
        }
        $result = rename($source_file_path, $file);
        if ($result !== true) {
            throw new Exception\RuntimeException(sprintf("File '%s' could not be renamed. " . 'An error occurred while processing the file.', $source_file_path));
        }
        return $file;
    }
    /**
     * @throws Exception\RuntimeException
     */
    public function filter(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }
        if (!file_exists($value)) {
            return $value;
        }
        foreach ($this->options as $option) {
            if (fnmatch($option['match'], $value)) {
                return $this->rename_file($value, $option);
            }
        }
        return $value;
    }
    /**
     * @param DefaultedOptionsSet $matchingOptions
     */
    private function get_file_name(string $file, array $matching_options): string
    {
        $file_info = pathinfo($file);
        $target_name = $matching_options['rename_to'] === '*' ? $file_info['basename'] : $matching_options['rename_to'];
        $target_dir = $matching_options['target_directory'] === '*' ? $file_info['dirname'] : $matching_options['target_directory'];
        $target = $target_dir . '/' . $target_name;
        if ($matching_options['randomize']) {
            $info = pathinfo($target);
            $new_target = $info['dirname'] . '/' . $info['filename'] . uniqid('_');
            if (isset($info['extension'])) {
                $new_target .= '.' . $info['extension'];
            }
            $target = $new_target;
        }
        return $target;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}