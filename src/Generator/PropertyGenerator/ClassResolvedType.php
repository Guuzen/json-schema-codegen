<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

final readonly class ClassResolvedType
{
    public function __construct(
        public string $className,
        public string $alias,
    ) {
    }
}
