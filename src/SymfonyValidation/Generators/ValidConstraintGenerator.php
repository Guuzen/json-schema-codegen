<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\Valid;

final readonly class ValidConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaResolver $resolver,
    ) {
    }

    public function generate(Schema $schema): ?Constraint
    {
        if (!$this->containsClassRef($schema)) {
            return null;
        }

        return new Constraint(Valid::class, []);
    }

    private function containsClassRef(Schema $schema): bool
    {
        $resolved = $this->resolver->resolved($schema);

        if ($resolved->ref !== null) {
            return true;
        }

        foreach ($resolved->oneOf ?? [] as $branch) {
            if ($this->containsClassRef($branch)) {
                return true;
            }
        }

        if ($resolved->items !== null && $this->containsClassRef($resolved->items)) {
            return true;
        }

        return false;
    }
}
