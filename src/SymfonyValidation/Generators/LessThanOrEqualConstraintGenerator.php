<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

final readonly class LessThanOrEqualConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaResolver $resolver,
    ) {
    }

    public function generate(Schema $schema): ?Constraint
    {
        $resolved = $this->resolver->resolved($schema);

        if ($resolved->type !== SchemaType::Integer || $resolved->maximum === null) {
            return null;
        }

        return new Constraint(LessThanOrEqual::class, [$resolved->maximum]);
    }
}
