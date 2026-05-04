<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\Choice;

final readonly class ChoiceConstraintGenerator implements ConstraintGenerator
{
    public function generate(PhpType $type): ?Constraint
    {
        if (!$type instanceof EnumLiteralType) {
            return null;
        }

        return new Constraint(Choice::class, ['choices' => $type->values]);
    }
}
