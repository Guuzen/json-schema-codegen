<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\SchemaWalker;
use Symfony\Component\Validator\Constraints\Choice;

final readonly class ChoiceConstraintGenerator implements ConstraintGenerator
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
            ...$this->schemaWalker->anyOf($schema, $this),
            ...$this->schemaWalker->ref($schema, $this),
        ];
    }

    /**
     * @return list<Constraint>
     */
    private function root(Schema $schema): array
    {
        $constraints = [];

        if ($schema->enum !== null) {
            $constraints[] = new Constraint(Choice::class, ['choices' => $schema->enum]);
        }

        return $constraints;
    }
}
