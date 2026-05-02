<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

final readonly class ClassRefType implements PhpType
{
    public function __construct(
        public string $alias,
        public string $fqcn,
    ) {
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function containsClassRef(): bool
    {
        return true;
    }
}
