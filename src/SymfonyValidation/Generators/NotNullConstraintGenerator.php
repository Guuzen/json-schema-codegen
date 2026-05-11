<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * NotNull is disjunctive: emit unless any branch admits null. The walker's
 * collect-and-concat model is conjunctive, so this generator keeps its own
 * predicate traversal instead of going through SchemaWalker.
 */
final readonly class NotNullConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaRegistry $registry,
    ) {
    }

    public function generate(Schema $schema): array
    {
        if ($this->isNullable($schema)) {
            return [];
        }

        return [new Constraint(NotNull::class, [])];
    }

    private function isNullable(Schema $schema): bool
    {
        if ($schema->type === SchemaType::Null) {
            return true;
        }

        if (is_array($schema->type) && in_array(SchemaType::Null, $schema->type, true)) {
            return true;
        }

        foreach ($schema->oneOf ?? [] as $branch) {
            if ($this->isNullable($branch)) {
                return true;
            }
        }

        if ($schema->ref !== null && $this->isNullable($this->registry->get($schema->ref->uri))) {
            return true;
        }

        return false;
    }
}
