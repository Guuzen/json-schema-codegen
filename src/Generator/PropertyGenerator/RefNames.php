<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Fqcn\Fqcn;

/**
 * Names a referenced type as it should appear inside one generated file.
 */
interface RefNames
{
    public function name(Fqcn $fqcn): string;
}
