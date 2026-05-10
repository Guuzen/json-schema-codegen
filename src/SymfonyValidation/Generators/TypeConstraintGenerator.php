<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\TypeRenderer;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ClassRef;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\Type;

final readonly class TypeConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private TypeRenderer $typeRenderer,
    ) {
    }

    public function generate(Schema $schema): ?Constraint
    {
        $values = [];

        foreach ($this->typeRenderer->render($schema) as $resolvedType) {
            if ($resolvedType->import === null) {
                $values[] = $resolvedType->type;
            } else {
                $values[] = new ClassRef(
                    fqcn: $resolvedType->import['fqcn'],
                    alias: $resolvedType->import['alias'],
                );
            }
        }

        $count = count($values);

        if ($count === 1) {
            return new Constraint(Type::class, [$values[0]]);
        }

        if ($count > 1) {
            return new Constraint(Type::class, [$values]);
        }

        return null;
    }
}
