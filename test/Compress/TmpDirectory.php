<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Compress;

use function assert;
use function is_dir;
use function is_string;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function rmdir;

use SplFileInfo;

use function unlink;

final class TmpDirectory
{
    public static function cleanUp(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::KEY_AS_PATHNAME),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $key => $item) {
            assert(is_string($key));
            assert($item instanceof SplFileInfo);
            if ($item->isFile()) {
                unlink($key);
                continue;
            }

            if ($item->isDir() && $item->getBasename() !== '.' && $item->getBasename() !== '..') {
                rmdir($key);
            }
        }

        rmdir($directory);
    }
}
