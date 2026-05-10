<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

final readonly class GreaterThanOrEqualConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaResolver $resolver,
    ) {
    }

    public function generate(Schema $schema): ?Constraint
    {
        $intSchema = $this->findInt($schema);
        if ($intSchema === null || $intSchema->minimum === null) {
            return null;
        }

        return new Constraint(GreaterThanOrEqual::class, [$intSchema->minimum]);
    }

    private function findInt(Schema $schema): ?Schema
    {
        $resolved = $this->resolver->resolved($schema);

        if ($resolved->type === SchemaType::Integer) {
            return $resolved;
        }

        if ($resolved->oneOf !== null) {
            return $this->findOnlyInt($resolved->oneOf);
        }

        if (is_array($resolved->type)) {
            return $this->findOnlyInt(array_map(
                fn(SchemaType $t) => new Schema(type: $t),
                $resolved->type,
            ));
        }

        return null;
    }

    /**
     * @param list<Schema> $branches
     */
    private function findOnlyInt(array $branches): ?Schema
    {
        $found = null;
        foreach ($branches as $branch) {
            $branch = $this->resolver->resolved($branch);
            if ($branch->type === SchemaType::Null) {
                continue;
            }
            if ($branch->type !== SchemaType::Integer) {
                return null;
            }
            if ($found !== null) {
                return null;
            }
            $found = $branch;
        }
        return $found;
    }
}
