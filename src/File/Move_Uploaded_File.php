<?php

declare (strict_types=1);
namespace Laminas\Filter\File;

use ErrorException;
use function is_uploaded_file;
use Laminas\Filter\Exception\InvalidArgumentException;
use function move_uploaded_file;
use function restore_error_handler;
use function set_error_handler;
/** @internal */
final class Move_Uploaded_File implements Move_Uploaded_File_Interface
{
    public function move_uploaded_file(string $source_file, string $target_file): void
    {
        if (!is_uploaded_file($source_file)) {
            throw new InvalidArgumentException('The source file is not an uploaded file');
        }
        set_error_handler(static function (int $error_number, string $message, $file = null, $line = null): never {
            throw new ErrorException($message, $error_number, $error_number, $file, $line);
        });
        try {
            move_uploaded_file($source_file, $target_file);
            return;
        } finally {
            restore_error_handler();
        }
    }
}