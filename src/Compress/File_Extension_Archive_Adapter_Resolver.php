<?php

declare (strict_types=1);
namespace Laminas\Filter\Compress;

use function basename;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\File_Information;
use function pathinfo;
use const PATHINFO_EXTENSION;
use function sprintf;
use function str_ends_with;
use function strtolower;
final class File_Extension_Archive_Adapter_Resolver implements Archive_Adapter_Resolver_Interface
{
    public function resolve(File_Information $file): Archive_Adapter_Interface
    {
        $file = strtolower(basename($file->path));
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $ext = $ext === '' ? $file : $ext;
        $extension = str_ends_with($file, 'tar.gz') ? 'tar' : $ext;
        $extension = str_ends_with($file, 'tar.bz2') ? 'tar' : $extension;
        return match ($extension) {
            'zip', 'zipx' => new Zip_Adapter(),
            'tar', 'tgz', 'tbz2' => new Tar_Adapter(),
            default => throw new RuntimeException(sprintf('Cannot handle the filename extension %s', $extension)),
        };
    }
}