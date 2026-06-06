<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\Fqcn;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;

final class ClassNameRefNames implements RefNames
{
    public function name(Fqcn $fqcn): string
    {
        return $fqcn->className();
    }
}
