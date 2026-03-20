<?php

declare (strict_types=1);
namespace Laminas\Filter\Compress;

interface Archive_Adapter_Interface
{
    /**
     * Compress a file into the given archive
     *
     * @param non-empty-string $archivePath The full path to the target archive
     * @param non-empty-string $filePath The full path to the file to compress
     */
    public function archive_file(string $archive_path, string $file_path): void;
    /**
     * Compress an arbitrary string as the contents of a file
     *
     * @param non-empty-string $archivePath The full path to the target archive
     * @param non-empty-string $fileName The basename of the target file within the archive
     * @param string $fileContents The contents of the compressed file
     */
    public function archive_string_to_file(string $archive_path, string $file_name, string $file_contents): void;
    /**
     * Compress the contents of a directory to an archive
     *
     * @param non-empty-string $archivePath The full path to the target archive
     * @param non-empty-string $directory The directory whose contents should be compressed
     */
    public function archive_directory_contents(string $archive_path, string $directory): void;
    /**
     * Decompress a file in the given archive
     *
     * @param non-empty-string $archivePath The full path of the archive to decompress
     * @param non-empty-string $targetDirectory The directory where the archive should be expanded
     */
    public function expand_archive(string $archive_path, string $target_directory): void;
}