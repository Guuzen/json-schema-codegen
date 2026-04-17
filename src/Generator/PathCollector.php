<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;

interface PathCollector
{
    /**
     * @return iterable<AbsoluteUnixPath>
     */
    public function collect(): iterable;
}
