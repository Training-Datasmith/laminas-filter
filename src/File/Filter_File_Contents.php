<?php

declare (strict_types=1);
namespace Laminas\Filter\File;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_writable;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\Filter_Interface;
use function sprintf;
/** @internal */
final readonly class Filter_File_Contents
{
    /** @param FilterInterface<string> $filter */
    public function __construct(private Filter_Interface $filter)
    {
    }
    /**
     * @throws InvalidArgumentException If no file exists.
     * @throws RuntimeException If the file cannot be written to or read from.
     */
    public function __invoke(string $file_path): void
    {
        if (!file_exists($file_path)) {
            throw new InvalidArgumentException(sprintf('File %s not found', $file_path));
        }
        if (!is_writable($file_path)) {
            throw new RuntimeException(sprintf('File "%s" is not writable', $file_path));
        }
        $content = file_get_contents($file_path);
        if ($content === false) {
            throw new RuntimeException(sprintf('The contents of "%s" could not be read', $file_path));
        }
        $result = file_put_contents($file_path, $this->filter->filter($content));
        if ($result === false) {
            throw new RuntimeException(sprintf('The file "%s" could not be written to', $file_path));
        }
    }
}