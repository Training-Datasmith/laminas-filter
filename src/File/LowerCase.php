<?php

declare (strict_types=1);
namespace Laminas\Filter\File;

use function is_array;
use function is_string;
use Laminas\Filter\Encoding_Option;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\Filter_Interface;
use Laminas\Filter\String_To_Lower;
/**
 * @psalm-type Options = array{encoding?: string}
 * @implements FilterInterface<mixed>
 */
final readonly class Lower_Case implements Filter_Interface
{
    private string $encoding;
    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->encoding = Encoding_Option::assert_with_default($options['encoding'] ?? null);
    }
    /**
     * Lowercases the contents of the given file path
     *
     * @param mixed $value Full path of file to change or $_FILES data array
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function filter(mixed $value): mixed
    {
        $file_path = null;
        if (is_string($value)) {
            $file_path = $value;
        }
        // An uploaded file? Retrieve the 'tmp_name'
        if (is_array($value)) {
            if (!isset($value['tmp_name']) || !is_string($value['tmp_name'])) {
                return $value;
            }
            $file_path = $value['tmp_name'];
        }
        if ($file_path === null) {
            return $value;
        }
        (new Filter_File_Contents(new String_To_Lower(['encoding' => $this->encoding])))($file_path);
        return $value;
    }
    /** @inheritDoc */
    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}