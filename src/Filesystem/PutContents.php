<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Filesystem;

use Guuzen\JsonSchemaCodegen\Generator\FileDumper;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;

final readonly class PutContents implements FileDumper
{
    public function dump(AbsoluteUnixPath $path, string $content): void
    {
        $outputFileDir = dirname($path->value);
        if (!@mkdir($outputFileDir, recursive: true) && !is_dir($outputFileDir)) {
            throw new \RuntimeException(sprintf('Failed to create output directory "%s"', $outputFileDir));
        }

        if (@file_put_contents($path->value, $content) === false) {
            throw new \RuntimeException(sprintf('Failed to write output file "%s"', $path->value));
        }
    }
}
