<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Filesystem;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Generator\PathCollector;

final readonly class PathsWithSuffix implements PathCollector
{
    /**
     * @param iterable<\SplFileInfo> $files
     * @param non-empty-string $suffix
     */
    public function __construct(
        private iterable $files,
        private string $suffix,
    ) {
    }

    public function collect(): iterable
    {
        foreach ($this->files as $file) {
            $absolutePath = $file->getPathname();

            if (str_ends_with($absolutePath, $this->suffix)) {
                yield new AbsoluteUnixPath($absolutePath);
            }
        }
    }

    /**
     * @param non-empty-string $suffix
     */
    public static function create(AbsoluteUnixDirectoryPath $schemaPath, string $suffix): self
    {
        return new self(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($schemaPath->value, \FilesystemIterator::SKIP_DOTS),
            ),
            $suffix,
        );
    }
}
