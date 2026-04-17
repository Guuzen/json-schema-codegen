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
        if (!is_dir($outputFileDir)) {
            mkdir($outputFileDir, recursive: true);
        }

        file_put_contents($path->value, $content);
    }
}
