<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function is_string;
use Laminas\Filter\Compress\Bz2Adapter;
use Laminas\Filter\Compress\Gz_Adapter;
use Laminas\Filter\Compress\String_Compression_Adapter_Interface;
use function strtolower;
/**
 * @psalm-type Options = array{
 *     adapter?: 'gz'|'bz2'|StringCompressionAdapterInterface,
 * }
 * @implements FilterInterface<string>
 */
final readonly class Decompress_String implements Filter_Interface
{
    private String_Compression_Adapter_Interface $adapter;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $adapter = $options['adapter'] ?? null;
        if ($adapter instanceof String_Compression_Adapter_Interface) {
            $this->adapter = $adapter;
        } else {
            /** @psalm-suppress RedundantFunctionCallGivenDocblockType This is for legacy compat with 'Gz2' and 'Bz' */
            $adapter = strtolower($adapter ?? 'gz');
            $this->adapter = match ($adapter) {
                'gz' => new Gz_Adapter(),
                'bz2' => new Bz2Adapter(),
            };
        }
    }
    public function filter(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }
        return $this->adapter->decompress($value);
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}