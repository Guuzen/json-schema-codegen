<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

final readonly class LessThanOrEqualConstraintGenerator implements ConstraintGenerator
{
    public function generate(PhpType $type): ?Constraint
    {
        if (!$type instanceof IntType || $type->max === null) {
            return null;
        }

        return new Constraint(LessThanOrEqual::class, [$type->max]);
    }
}
