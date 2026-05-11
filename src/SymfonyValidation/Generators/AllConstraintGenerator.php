<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\DefaultTypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintList;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\SchemaWalker;
use Symfony\Component\Validator\Constraints\All;

final readonly class AllConstraintGenerator implements ConstraintGenerator
{
    /**
     * @param list<ConstraintGenerator> $generators
     */
    public function __construct(
        private array $generators,
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
        if (!$schema->isArray()) {
            return [];
        }

        $itemSchema = $schema->items ?? new Schema();
        $itemConstraints = [];

        foreach ($this->generators as $factory) {
            $constraints = $factory->generate($itemSchema);
            $itemConstraints = [...$itemConstraints, ...$constraints];
        }

        if ($itemConstraints === []) {
            return [];
        }

        return [new Constraint(All::class, [new ConstraintList($itemConstraints)])];
    }

    /**
     * @return list<ConstraintGenerator>
     */
    public static function defaultGenerators(
        FqcnResolver   $fqcnResolver,
        SchemaRegistry $registry,
        SchemaWalker   $schemaWalker,
    ): array {
        return [
            new TypeConstraintGenerator(new DefaultTypeRenderer($fqcnResolver, $registry)),
            new NotNullConstraintGenerator($registry),
            new UuidConstraintGenerator($schemaWalker),
            new DateConstraintGenerator($schemaWalker),
            new DateTimeConstraintGenerator($schemaWalker),
            new RegexConstraintGenerator($schemaWalker),
            new GreaterThanOrEqualConstraintGenerator($schemaWalker),
            new LessThanOrEqualConstraintGenerator($schemaWalker),
            new ChoiceConstraintGenerator($schemaWalker),
        ];
    }
}
