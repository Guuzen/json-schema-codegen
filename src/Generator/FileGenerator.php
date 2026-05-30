<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

interface FileGenerator
{
    public function generate(AbsoluteUri $schemaUri, SchemaTree $tree): ?string;
}
