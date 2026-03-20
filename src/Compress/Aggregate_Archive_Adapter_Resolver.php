<?php

declare (strict_types=1);
namespace Laminas\Filter\Compress;

use function array_values;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\File\File_Information;
use function sprintf;
final readonly class Aggregate_Archive_Adapter_Resolver implements Archive_Adapter_Resolver_Interface
{
    /** @var list<ArchiveAdapterResolverInterface> */
    private array $matchers;
    public function __construct(Archive_Adapter_Resolver_Interface ...$matchers)
    {
        $this->matchers = array_values($matchers);
    }
    public function resolve(File_Information $file): Archive_Adapter_Interface
    {
        foreach ($this->matchers as $matcher) {
            try {
                return $matcher->resolve($file);
            } catch (RuntimeException) {
            }
        }
        throw new RuntimeException(sprintf('No matchers were able to resolve the file %s', $file->path));
    }
}