<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function is_dir;
use function is_string;
use Laminas\Filter\Compress\Archive_Adapter_Interface;
use Laminas\Filter\Compress\Tar_Adapter;
use Laminas\Filter\Compress\Zip_Adapter;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\File_Information;
/**
 * @psalm-type Options = array{
 *     archive: non-empty-string,
 *     adapter?: ArchiveAdapterInterface|'zip'|'tar'|null,
 *     fileName?: non-empty-string|null,
 * }
 * @implements FilterInterface<non-empty-string>
 */
final readonly class Compress_To_Archive implements Filter_Interface
{
    private Archive_Adapter_Interface $adapter;
    /** @var non-empty-string */
    private string $archive;
    /** @var non-empty-string|null */
    private string|null $file_name;
    /** @param Options $options */
    public function __construct(array $options)
    {
        $adapter = $options['adapter'] ?? null;
        if (!$adapter instanceof Archive_Adapter_Interface) {
            $adapter ??= 'zip';
            $adapter = match ($adapter) {
                'zip' => new Zip_Adapter(),
                'tar' => new Tar_Adapter(),
            };
        }
        $this->adapter = $adapter;
        $this->archive = $options['archive'];
        $this->file_name = $options['fileName'] ?? null;
    }
    /**
     * Compress content to an archive
     *
     * Given a file path, the file will be compressed into the configured archive. When a directory path is
     * encountered, the directory contents will be compressed to the configured archive. When arbitrary strings are
     * received, they are treated as the contents of the file to compress. In this case, a target file name _must_ be
     * provided in the options.
     *
     * File paths can be passed as either a string, a PHP $_FILES array, or, a PSR UploadedFileInterface
     *
     * The return value is the full path to the archive containing the file/s or string provided, or, the un-filtered
     * input if filtering is not possible.
     *
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && is_dir($value)) {
            /** @psalm-var non-empty-string $value This is required for now. Psalm cannot seem to infer the type here. */
            $this->adapter->archive_directory_contents($this->archive, $value);
            return $this->archive;
        }
        if (File_Information::is_possible_file($value)) {
            $file = File_Information::factory($value);
            $this->adapter->archive_file($this->archive, $file->path);
            return $this->archive;
        }
        if (!is_string($value)) {
            return $value;
        }
        if ($this->file_name === null) {
            throw new RuntimeException('The `fileName` option must be present when compressing arbitrary strings');
        }
        $this->adapter->archive_string_to_file($this->archive, $this->file_name, $value);
        return $this->archive;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}