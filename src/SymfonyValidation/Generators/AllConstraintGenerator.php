<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\DefaultTypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintList;
use Symfony\Component\Validator\Constraints\All;

final readonly class AllConstraintGenerator implements ConstraintGenerator
{
    /**
     * @param list<ConstraintGenerator> $generators
     */
    public function __construct(
        private array $generators,
    )
    {
    }

    public function generate(Schema $schema): ?Constraint
    {
        if ($schema->type !== SchemaType::Array) {
            return null;
        }

        $itemSchema = $schema->items ?? new Schema();
        $itemConstraints = [];

        foreach ($this->generators as $factory) {
            $constraint = $factory->generate($itemSchema);
            if ($constraint !== null) {
                $itemConstraints[] = $constraint;
            }
        }

        if ($itemConstraints === []) {
            return null;
        }

        return new Constraint(All::class, [new ConstraintList($itemConstraints)]);
    }

    /**
     * @return list<ConstraintGenerator>
     */
    public static function defaultGenerators(
        SchemaResolver $resolver,
        FqcnResolver   $fqcnResolver,
        SchemaRegistry $registry,
    ): array {
        return [
            new TypeConstraintGenerator(new DefaultTypeRenderer($fqcnResolver, $registry)),
            new NotNullConstraintGenerator($resolver),
            new UuidConstraintGenerator($resolver),
            new DateConstraintGenerator($resolver),
            new DateTimeConstraintGenerator($resolver),
            new RegexConstraintGenerator($resolver),
            new GreaterThanOrEqualConstraintGenerator($resolver),
            new LessThanOrEqualConstraintGenerator($resolver),
            new ChoiceConstraintGenerator($resolver),
        ];
    }
}
