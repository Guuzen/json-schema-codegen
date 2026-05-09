<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\Choice;

final readonly class ChoiceConstraintGenerator implements ConstraintGenerator
{
    public function generate(PhpType $type): ?Constraint
    {
        $enum = $this->findEnum($type);
        if ($enum === null) {
            return null;
        }

        return new Constraint(Choice::class, ['choices' => $enum->values]);
    }

    private function findEnum(PhpType $type): ?EnumLiteralType
    {
        if ($type instanceof EnumLiteralType) {
            return $type;
        }

        if (!$type instanceof UnionType) {
            return null;
        }

        return $type->findOnlySingleType(EnumLiteralType::class);
    }
}
