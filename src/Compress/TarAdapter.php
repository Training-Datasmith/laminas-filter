<?php

declare (strict_types=1);
namespace Laminas\Filter\Compress;

use Archive_Tar;
use function assert;
use function class_exists;
use function dirname;
use function extension_loaded;
use function file_exists;
use function is_dir;
use function is_string;
use Laminas\Filter\Exception\Extension_Not_Loaded_Exception;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Recursive_Directory_Iterator;
use Recursive_Iterator_Iterator;
use Spl_File_Info;
use function sprintf;
use function strtolower;
/**
 * @psalm-type Options = array{
 *     mode?: 'gz'|'bz2'|'GZ'|'BZ2'|null,
 * }
 */
final readonly class Tar_Adapter implements Archive_Adapter_Interface
{
    /** @var 'gz'|'bz2' */
    private string $mode;
    /**
     * @param Options $options
     * @throws ExtensionNotLoadedException If Archive_Tar component not available.
     */
    public function __construct(array $options = [])
    {
        if (!class_exists('Archive_Tar')) {
            throw new Extension_Not_Loaded_Exception('This filter needs PEAR\'s Archive_Tar component. ' . 'Ensure loading Archive_Tar (registering autoload or require_once)');
        }
        /** @psalm-var 'gz'|'bz2' $mode */
        $mode = strtolower($options['mode'] ?? 'gz');
        $this->mode = $mode;
        if ($this->mode === 'bz2' && !extension_loaded('bz2')) {
            throw new Extension_Not_Loaded_Exception('This mode needs the bz2 extension');
        }
        if ($this->mode === 'gz' && !extension_loaded('zlib')) {
            throw new Extension_Not_Loaded_Exception('This mode needs the zlib extension');
        }
    }
    public function archive_file(string $archive_path, string $file_path): void
    {
        if (!file_exists($file_path)) {
            throw new InvalidArgumentException(sprintf('The file %s does not exist', $file_path));
        }
        $archive = new Archive_Tar($archive_path, $this->mode);
        $result = $archive->create_modify([$file_path], '', dirname($file_path));
        if ($result === false) {
            throw new RuntimeException('Error creating the Tar archive');
        }
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
        $archive = new Archive_Tar($archive_path, $this->mode);
        $result = $archive->create_modify($files, '', $directory);
        if ($result === false) {
            throw new RuntimeException('Error creating the Tar archive');
        }
    }
    public function archive_string_to_file(string $archive_path, string $file_name, string $file_contents): void
    {
        $archive = new Archive_Tar($archive_path, $this->mode);
        $result = $archive->add_string($file_name, $file_contents);
        if ($result === false) {
            throw new RuntimeException('Error creating the Tar archive');
        }
    }
    public function expand_archive(string $archive_path, string $target_directory): void
    {
        if (!file_exists($archive_path)) {
            throw new InvalidArgumentException(sprintf('An archive does not exist at %s', $archive_path));
        }
        $archive = new Archive_Tar($archive_path);
        $result = $archive->extract($target_directory);
        if ($result === false) {
            throw new RuntimeException('Error while extracting the Tar archive');
        }
    }
}