<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\PhpType;

interface TypeGenerator
{
    /**
     * @return list<ResolvedType>
     */
    public function generate(PhpType $type): array;
}
