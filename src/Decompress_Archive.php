<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function is_dir;
use function is_writable;
use Laminas\Filter\Compress\Aggregate_Archive_Adapter_Resolver;
use Laminas\Filter\Compress\Archive_Adapter_Resolver_Interface;
use Laminas\Filter\Compress\File_Extension_Archive_Adapter_Resolver;
use Laminas\Filter\Compress\Mime_Type_Archive_Adapter_Resolver;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\File_Information;
/**
 * @psalm-type Options = array{
 *     target: non-empty-string,
 *     matcher?: ArchiveAdapterResolverInterface,
 * }
 * @implements FilterInterface<non-empty-string>
 */
final readonly class Decompress_Archive implements Filter_Interface
{
    /** @var non-empty-string */
    private string $target;
    private Archive_Adapter_Resolver_Interface $matcher;
    /** @param Options $options */
    public function __construct(array $options)
    {
        $target = $options['target'];
        if (!is_dir($target) || !is_writable($target)) {
            throw new InvalidArgumentException('The target directory %s is either not a directory, or it cannot be written to');
        }
        $this->target = $target;
        $this->matcher = $options['matcher'] ?? new Aggregate_Archive_Adapter_Resolver(new Mime_Type_Archive_Adapter_Resolver(), new File_Extension_Archive_Adapter_Resolver());
    }
    public function filter(mixed $value): mixed
    {
        if (!File_Information::is_possible_file($value)) {
            return $value;
        }
        $file = File_Information::factory($value);
        try {
            $adapter = $this->matcher->resolve($file);
        } catch (RuntimeException) {
            return $value;
        }
        $adapter->expand_archive($file->path, $this->target);
        return $this->target;
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}