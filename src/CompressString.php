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
 *     level?: int<1,9>|null,
 * }
 * @implements FilterInterface<string>
 */
final readonly class Compress_String implements Filter_Interface
{
    private String_Compression_Adapter_Interface $adapter;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $adapter = $options['adapter'] ?? null;
        $level = $options['level'] ?? null;
        if ($adapter instanceof String_Compression_Adapter_Interface) {
            $this->adapter = $adapter;
        } else {
            /** @psalm-suppress RedundantFunctionCallGivenDocblockType This is for legacy compat with 'Gz2' and 'Bz' */
            $adapter = strtolower($adapter ?? 'gz');
            $this->adapter = match ($adapter) {
                'gz' => new Gz_Adapter(['level' => $level]),
                'bz2' => new Bz2Adapter(['blocksize' => $level]),
            };
        }
    }
    public function filter(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }
        return $this->adapter->compress($value);
    }
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}