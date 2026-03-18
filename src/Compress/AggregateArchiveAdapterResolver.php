<?php

declare(strict_types=1);

namespace Laminas\Filter\Compress;

use function array_values;

use Laminas\Filter\Exception\RuntimeException;

use Laminas\Filter\File\FileInformation;

use function sprintf;

final readonly class AggregateArchiveAdapterResolver implements ArchiveAdapterResolverInterface
{
    /** @var list<ArchiveAdapterResolverInterface> */
    private array $matchers;

    public function __construct(ArchiveAdapterResolverInterface ...$matchers)
    {
        $this->matchers = array_values($matchers);
    }

    public function resolve(FileInformation $file): ArchiveAdapterInterface
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
