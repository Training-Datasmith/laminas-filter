<?php

declare (strict_types=1);
namespace Laminas\Filter\Compress;

use function assert;
use function bzcompress;
use function bzdecompress;
use function extension_loaded;
use function is_int;
use Laminas\Filter\Exception\Extension_Not_Loaded_Exception;
use Laminas\Filter\Exception\RuntimeException;
use function sprintf;
/**
 * Compression adapter for Bz2
 *
 * @psalm-type Options = array{
 *     blocksize?: int<1, 9>|null,
 * }
 */
final readonly class Bz2Adapter implements String_Compression_Adapter_Interface
{
    private const DEFAULT_BLOCK_SIZE = 4;
    /** @var int<1, 9> */
    private int $block_size;
    /**
     * @param Options $options (Optional) Options to set
     * @throws ExtensionNotLoadedException If bz2 extension not loaded.
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('bz2')) {
            throw new Extension_Not_Loaded_Exception('This filter needs the bz2 extension');
        }
        $this->block_size = $options['blocksize'] ?? self::DEFAULT_BLOCK_SIZE;
    }
    public function compress(string $value): string
    {
        $compressed = bzcompress($value, $this->block_size);
        if (is_int($compressed)) {
            throw new RuntimeException(sprintf('Error during compression: Bz Error code %d', $compressed));
        }
        assert($compressed !== '');
        return $compressed;
    }
    public function decompress(string $value): string
    {
        $decompressed = bzdecompress($value);
        if (is_int($decompressed) || $decompressed === false) {
            throw new RuntimeException(sprintf('Error during decompression: Bz Error code %d', (string) $decompressed));
        }
        assert($decompressed !== '');
        return $decompressed;
    }
}