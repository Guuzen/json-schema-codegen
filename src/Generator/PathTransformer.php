<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;

interface PathTransformer
{
    public function generate(AbsoluteUnixPath $path): AbsoluteUnixPath;
}
