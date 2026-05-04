<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation;

final readonly class ConstraintList
{
    /**
     * @param list<Constraint> $constraints
     */
    public function __construct(
        public array $constraints,
    )
    {
    }
}
