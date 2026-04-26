<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType;

final readonly class ClassRefType implements PhpType
{
    public function __construct(
        public string $alias,
        public string $fqcn,
    ) {
    }
}
