<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation;

final readonly class Constraint
{
    /**
     * @param array<int|string, mixed|ClassRef|array|ConstraintList> $args
     */
    public function __construct(
        public string $name,
        public array $args,
    )
    {
    }
}
