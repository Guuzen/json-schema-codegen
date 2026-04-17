<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;

interface FileLoader
{
    public function load(AbsoluteUnixPath $path): string;
}
