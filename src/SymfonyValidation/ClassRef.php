<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation;

final readonly class ClassRef
{
    public function __construct(
        public string $fqcn,
        public string $alias,
    )
    {
    }
}
