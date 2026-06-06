<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

interface DefaultGenerator
{
    public function generate(Schema $schema, RefNames $refNames): ?DefaultValue;
}
