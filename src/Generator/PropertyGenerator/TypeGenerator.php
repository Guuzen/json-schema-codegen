<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\PhpType;

interface TypeGenerator
{
    public function generate(PhpType $type): ResolvedTypes;
}
