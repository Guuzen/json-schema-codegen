<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintList;
use Symfony\Component\Validator\Constraints\All;

final readonly class AllConstraintGenerator implements ConstraintGenerator
{
    /**
     * @param list<ConstraintGenerator> $itemFactories
     */
    public function __construct(
        private array $itemFactories,
    )
    {
    }

    public function generate(PhpType $type): ?Constraint
    {
        if (!$type instanceof ListType) {
            return null;
        }

        $itemConstraints = [];

        foreach ($this->itemFactories as $factory) {
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
}
