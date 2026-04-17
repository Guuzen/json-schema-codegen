<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;

final readonly class Config
{
    /**
     * @param non-empty-string $schemaSuffix
     */
    public function __construct(
        public string $baseNamespace,
        public AbsoluteUnixDirectoryPath $schemaPath,
        public AbsoluteUnixDirectoryPath $outputPath,
        public string $schemaSuffix,
    ) {
    }
}
