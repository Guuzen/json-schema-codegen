<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\Choice;

final readonly class ChoiceConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaResolver $resolver,
    ) {
    }

    public function generate(Schema $schema): ?Constraint
    {
        $enum = $this->findEnum($schema);
        if ($enum === null) {
            return null;
        }

        return new Constraint(Choice::class, ['choices' => $enum]);
    }

    /**
     * @return list<string|int>|null
     */
    private function findEnum(Schema $schema): ?array
    {
        $resolved = $this->resolver->resolved($schema);

        if ($resolved->enum !== null) {
            return $resolved->enum;
        }

        if ($resolved->oneOf === null) {
            return null;
        }

        $found = null;
        foreach ($resolved->oneOf as $branch) {
            $branch = $this->resolver->resolved($branch);
            if ($branch->type === SchemaType::Null) {
                continue;
            }
            if ($branch->enum === null) {
                return null;
            }
            if ($found !== null) {
                return null;
            }
            $found = $branch->enum;
        }

        return $found;
    }
}
