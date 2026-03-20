<?php

declare (strict_types=1);
namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\File_Information;
use function sprintf;
final class Mime_Type_Archive_Adapter_Resolver implements Archive_Adapter_Resolver_Interface
{
    public function resolve(File_Information $file): Archive_Adapter_Interface
    {
        $type = $file->detect_mime_type();
        return match ($type) {
            'application/zip' => new Zip_Adapter(),
            'application/x-tar' => new Tar_Adapter(),
            default => throw new RuntimeException(sprintf('Cannot handle the mime type %s', $type)),
        };
    }
}