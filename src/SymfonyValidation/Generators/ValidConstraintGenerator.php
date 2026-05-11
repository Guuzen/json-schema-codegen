<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\SchemaWalker;
use Symfony\Component\Validator\Constraints\Valid;

final readonly class ValidConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaWalker $schemaWalker,
        private SchemaRegistry $registry,
    )
    {
    }

    public function generate(Schema $schema): array
    {
        $constraints = [
            ...$this->root($schema),
            ...$this->schemaWalker->oneOf($schema, $this),
            ...$this->schemaWalker->ref($schema, $this),
            ...($schema->items !== null ? $this->generate($schema->items) : []),
        ];

        // Valid has no args — every emission is identical, so collapse duplicates.
        return $constraints === [] ? [] : [new Constraint(Valid::class, [])];
    }

    /**
     * @return list<Constraint>
     */
    private function root(Schema $schema): array
    {
        if ($schema->ref === null) {
            return [];
        }

        if ($this->registry->get($schema->ref->uri)->type !== SchemaType::Object) {
            return [];
        }

        return [new Constraint(Valid::class, [])];
    }
}
