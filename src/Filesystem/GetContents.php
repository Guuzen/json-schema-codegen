<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Filesystem;

use Guuzen\JsonSchemaCodegen\Generator\FileLoader;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;

final readonly class GetContents implements FileLoader
{
    public function load(AbsoluteUnixPath $path): string
    {
        $content = file_get_contents($path->value);

        if ($content === false) {
            throw new \RuntimeException(sprintf('Failed to read file "%s"', $path->value));
        }

        return $content;
    }
}
