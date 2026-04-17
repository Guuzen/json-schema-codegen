<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;

interface FileDumper
{
    public function dump(AbsoluteUnixPath $path, string $content): void;
}
