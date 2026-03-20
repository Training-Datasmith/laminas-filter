<?php

declare (strict_types=1);
namespace Laminas\Filter\File;

use Throwable;
interface Move_Uploaded_File_Interface
{
    /**
     * @param non-empty-string $sourceFile
     * @param non-empty-string $targetFile
     * @throws Throwable If any kind of failure occurs that prevents the file from being moved.
     */
    public function move_uploaded_file(string $source_file, string $target_file): void;
}