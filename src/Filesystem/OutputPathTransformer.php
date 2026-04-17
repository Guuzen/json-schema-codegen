<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Filesystem;

use Guuzen\JsonSchemaCodegen\Generator\PathTransformer;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\RelativeUnixPath;

final readonly class OutputPathTransformer implements PathTransformer
{
    /**
     * @param non-empty-string $schemaSuffix
     */
    public function __construct(
        private AbsoluteUnixDirectoryPath $schemaPath,
        private AbsoluteUnixDirectoryPath $outputPath,
        private string $schemaSuffix,
    ) {
    }

    public function generate(AbsoluteUnixPath $path): AbsoluteUnixPath
    {
        $relativePath = $path->relativeTo($this->schemaPath);
        $withoutSuffix = str_ends_with($relativePath->value, $this->schemaSuffix)
            ? substr($relativePath->value, 0, -strlen($this->schemaSuffix))
            : $relativePath->value;

        return $this->outputPath->resolve(new RelativeUnixPath($withoutSuffix . '.php'));
    }
}
