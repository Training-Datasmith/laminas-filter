<?php

declare (strict_types=1);
namespace Laminas\Filter\File;

use function array_pop;
use function basename;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function explode;
use function file_exists;
use function implode;
use function is_dir;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\Filter_Interface;
use function pathinfo;
use const PATHINFO_DIRNAME;
use const PATHINFO_EXTENSION;
use function sprintf;
use function str_contains;
use function str_ends_with;
use Throwable;
use function uniqid;
use function unlink;
/**
 * @psalm-type Options = array{
 *     target?: non-empty-string,
 *     use_upload_name?: bool,
 *     use_upload_extension?: bool,
 *     overwrite?: bool,
 *     randomize?: bool,
 * }
 * @implements FilterInterface<string>
 */
final readonly class Rename_Upload implements Filter_Interface
{
    private string $target_directory;
    private string $target_filename;
    private bool $use_upload_name;
    private bool $use_upload_extension;
    private bool $overwrite;
    private bool $randomize;
    private Move_Uploaded_File_Interface $move_uploaded_file;
    /** @param Options $options */
    public function __construct(array $options = [], Move_Uploaded_File_Interface|null $move_uploaded_file = null)
    {
        $target = $this->resolve_target_options($options['target'] ?? '*');
        $this->target_directory = $target['directory'];
        $this->target_filename = $target['filename'];
        $this->use_upload_name = $options['use_upload_name'] ?? false;
        $this->use_upload_extension = $options['use_upload_extension'] ?? false;
        $this->overwrite = $options['overwrite'] ?? false;
        $this->randomize = $options['randomize'] ?? false;
        $this->move_uploaded_file = $move_uploaded_file ?? new Move_Uploaded_File();
    }
    /**
     * Figure out a target directory and a target filename from the given string
     *
     * @param non-empty-string $option
     * @return array{directory: non-empty-string, filename: non-empty-string}
     */
    private function resolve_target_options(string $option): array
    {
        if ($option === '*') {
            return ['directory' => '*', 'filename' => '*'];
        }
        if (is_dir($option)) {
            return ['directory' => $option, 'filename' => '*'];
        }
        // Assume the last path component is a filename, if it contains a dot
        $file = basename($option);
        if (!str_contains($file, '.')) {
            $dir = $option;
            $file = '*';
        } else {
            $dir = pathinfo($option, PATHINFO_DIRNAME);
        }
        if (!is_dir($dir) || $dir === '') {
            throw new InvalidArgumentException(sprintf('Cannot resolve a target directory from the option: "%s"', $option));
        }
        return ['directory' => $dir, 'filename' => $file];
    }
    public function filter(mixed $value): mixed
    {
        if (!File_Information::is_possible_file($value)) {
            return $value;
        }
        $file = File_Information::factory($value);
        $target = $this->resolve_target_filename($file);
        if (file_exists($target)) {
            if ($this->overwrite === false) {
                throw new RuntimeException(sprintf("File '%s' could not be renamed. It already exists.", $target));
            }
            unlink($target);
        }
        $this->move_uploaded_file($file->path, $target);
        return $target;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
    /**
     * @throws RuntimeException
     * @param non-empty-string $sourceFile
     * @param non-empty-string $targetFile
     */
    private function move_uploaded_file(string $source_file, string $target_file): void
    {
        try {
            $this->move_uploaded_file->move_uploaded_file($source_file, $target_file);
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf("File '%s' could not be renamed. An error occurred while processing the file.", $source_file), 0, $e);
        }
    }
    /** @return non-empty-string */
    private function resolve_target_filename(File_Information $file): string
    {
        $target_directory = $this->target_directory;
        if ($target_directory === '*') {
            $target_directory = dirname($file->path);
        }
        $target_filename = $this->target_filename;
        if ($target_filename === '*') {
            if ($this->use_upload_name && $file->client_file_name !== null) {
                $target_filename = basename($file->client_file_name);
            } else {
                $target_filename = $file->base_name;
            }
        }
        $extension = $this->target_filename !== '*' ? pathinfo($this->target_filename, PATHINFO_EXTENSION) : '';
        $extension = $this->use_upload_extension ? $this->resolve_extension($file) : $extension;
        if ($extension !== '' && !str_ends_with($target_filename, '.' . $extension)) {
            $target_filename .= '.' . $extension;
        }
        if ($this->randomize) {
            $target_filename = $this->randomize_filename($target_filename);
        }
        return $target_directory . DIRECTORY_SEPARATOR . $target_filename;
    }
    /**
     * Return a filename extension, purely based on information in the upload
     *
     * Note: possibly empty string is returned
     */
    private function resolve_extension(File_Information $file): string
    {
        if ($this->use_upload_extension && $file->client_file_name !== null) {
            $ext = pathinfo($file->client_file_name, PATHINFO_EXTENSION);
            if ($ext !== '') {
                return $ext;
            }
        }
        return pathinfo($file->path, PATHINFO_EXTENSION);
    }
    private function randomize_filename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $extension = $extension === '' ? '' : '.' . $extension;
        $basename = $filename;
        if ($extension !== '') {
            $parts = explode('.', $filename);
            array_pop($parts);
            $basename = implode('.', $parts);
        }
        return sprintf('%s_%s%s', $basename, uniqid(), $extension);
    }
}