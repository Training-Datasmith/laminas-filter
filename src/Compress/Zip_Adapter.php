<?php

declare (strict_types=1);
namespace Laminas\Filter\Compress;

use function assert;
use function basename;
use const DIRECTORY_SEPARATOR;
use function extension_loaded;
use function file_put_contents;
use function is_dir;
use function is_int;
use function is_string;
use Laminas\Filter\Exception\Extension_Not_Loaded_Exception;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use function ltrim;
use Recursive_Directory_Iterator;
use Recursive_Iterator_Iterator;
use Spl_File_Info;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use Zip_Archive;
final class Zip_Adapter implements Archive_Adapter_Interface
{
    /**
     * @throws ExtensionNotLoadedException If zip extension not loaded.
     */
    public function __construct()
    {
        if (!extension_loaded('zip')) {
            throw new Extension_Not_Loaded_Exception('This filter needs the zip extension');
        }
    }
    public function archive_file(string $archive_path, string $file_path): void
    {
        $zip = $this->open_archive($archive_path);
        $result = $zip->add_file($file_path, basename($file_path));
        if ($result === false) {
            throw new RuntimeException(sprintf('Failed to add the file %s to the archive', $file_path));
        }
        $zip->close();
    }
    public function archive_string_to_file(string $archive_path, string $file_name, string $file_contents): void
    {
        $file_path = sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, basename($file_name));
        $result = file_put_contents($file_path, $file_contents);
        if ($result === false) {
            throw new RuntimeException('Failed to write contents to a temporary file');
        }
        $this->archive_file($archive_path, $file_path);
    }
    public function archive_directory_contents(string $archive_path, string $directory): void
    {
        if (!is_dir($directory)) {
            throw new InvalidArgumentException('The directory argument is not a directory');
        }
        $iterator = new Recursive_Iterator_Iterator(new Recursive_Directory_Iterator($directory, Recursive_Directory_Iterator::KEY_AS_PATHNAME | \Filesystem_Iterator::SKIP_DOTS), Recursive_Iterator_Iterator::SELF_FIRST);
        $files = [];
        foreach ($iterator as $key => $item) {
            assert(is_string($key));
            assert($item instanceof Spl_File_Info);
            if (!$item->is_file()) {
                continue;
            }
            $files[] = $key;
        }
        $zip = $this->open_archive($archive_path);
        foreach ($files as $file_path) {
            // Relative file path should use '/' separators inside the archive
            $entry = str_replace($directory, '', $file_path);
            $entry = ltrim(str_replace('\\', '/', $entry), '/');
            $result = $zip->add_file($file_path, $entry);
            if ($result === false) {
                throw new RuntimeException(sprintf('Failed to add the file %s to the archive', $file_path));
            }
        }
        $zip->close();
    }
    public function expand_archive(string $archive_path, string $target_directory): void
    {
        $zip = new Zip_Archive();
        $zip->open($archive_path);
        $result = $zip->extract_to($target_directory);
        $zip->close();
        if ($result === false) {
            throw new RuntimeException(sprintf('Failed to extract archive to the target directory: %s', $target_directory));
        }
    }
    private function open_archive(string $archive_path): Zip_Archive
    {
        $zip = new Zip_Archive();
        $result = $zip->open($archive_path, Zip_Archive::CREATE | Zip_Archive::OVERWRITE);
        if (is_int($result)) {
            throw new RuntimeException(sprintf('The archive could not be opened. Error code %d', $result));
        }
        return $zip;
    }
}