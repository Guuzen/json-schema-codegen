<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\RelativeUnixPath;

final readonly class Config
{
    /**
     * @param non-empty-string            $schemaSuffix
     * @param array<string, class-string> $typeMappings relative schema path => fully-qualified type name,
     *                              e.g. ['DateTimeImmutable.json' => '\DateTimeImmutable']. Mapped
     *                              schemas are referenced as that type and never generated.
     */
    public function __construct(
        public string $baseNamespace,
        public AbsoluteUnixDirectoryPath $schemaPath,
        public AbsoluteUnixDirectoryPath $outputPath,
        public string $schemaSuffix,
        public RelativeUnixPath $undefinedPath,
        public array $typeMappings = [],
    )
    {
    }
}
