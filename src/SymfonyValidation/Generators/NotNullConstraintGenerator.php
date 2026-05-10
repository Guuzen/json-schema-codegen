<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\NotNull;

final readonly class NotNullConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaResolver $resolver,
    ) {
    }

    public function generate(Schema $schema): ?Constraint
    {
        if ($this->isNullable($schema)) {
            return null;
        }

        return new Constraint(NotNull::class, []);
    }

    private function isNullable(Schema $schema): bool
    {
        $resolved = $this->resolver->resolved($schema);

        if ($resolved->type === SchemaType::Null) {
            return true;
        }

        if (is_array($resolved->type) && in_array(SchemaType::Null, $resolved->type, true)) {
            return true;
        }

        foreach ($resolved->oneOf ?? [] as $branch) {
            if ($this->isNullable($branch)) {
                return true;
            }
        }

        return false;
    }
}
