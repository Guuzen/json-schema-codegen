<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

final readonly class GreaterThanOrEqualConstraintGenerator implements ConstraintGenerator
{
    public function generate(PhpType $type): ?Constraint
    {
        $intType = $this->findIntType($type);
        if ($intType === null || $intType->min === null) {
            return null;
        }

        return new Constraint(GreaterThanOrEqual::class, [$intType->min]);
    }

    private function findIntType(PhpType $type): ?IntType
    {
        if ($type instanceof IntType) {
            return $type;
        }

        if (!$type instanceof UnionType) {
            return null;
        }

        return $type->findOnlySingleType(IntType::class);
    }
}
