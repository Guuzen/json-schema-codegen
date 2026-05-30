<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

use Guuzen\JsonSchemaCodegen\Generator\SchemaTree;

interface DefaultGenerator
{
    public function generate(SchemaTree $tree): ?DefaultValue;
}
