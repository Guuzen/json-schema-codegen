<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\SchemaWalker;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

final readonly class LessThanOrEqualConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaWalker $schemaWalker,
    )
    {
    }

    public function generate(Schema $schema): array
    {
        return [
            ...$this->root($schema),
            ...$this->schemaWalker->oneOf($schema, $this),
            ...$this->schemaWalker->ref($schema, $this),
        ];
    }

    /**
     * @return list<Constraint>
     */
    private function root(Schema $schema): array
    {
        $constraints = [];

        if ($schema->maximum !== null && $schema->isInteger()) {
            $constraints[] = new Constraint(LessThanOrEqual::class, [$schema->maximum]);
        }

        return $constraints;
    }
}
