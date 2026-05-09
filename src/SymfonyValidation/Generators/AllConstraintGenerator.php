<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\DefaultTypeRenderer;
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

    public function generate(PhpType $type): ?Constraint
    {
        if (!$type instanceof ListType) {
            return null;
        }

        $itemConstraints = [];

        foreach ($this->generators as $factory) {
            $constraint = $factory->generate($type->itemType);
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
    public static function defaultGenerators(): array
    {
        return [
            new TypeConstraintGenerator(new DefaultTypeRenderer()),
            new NotNullConstraintGenerator(),
            new UuidConstraintGenerator(),
            new DateConstraintGenerator(),
            new DateTimeConstraintGenerator(),
            new RegexConstraintGenerator(),
            new GreaterThanOrEqualConstraintGenerator(),
            new LessThanOrEqualConstraintGenerator(),
            new ChoiceConstraintGenerator(),
        ];
    }
}
