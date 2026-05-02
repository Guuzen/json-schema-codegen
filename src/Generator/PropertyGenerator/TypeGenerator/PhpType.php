<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator;

interface PhpType
{
    public function isNullable(): bool;
}
