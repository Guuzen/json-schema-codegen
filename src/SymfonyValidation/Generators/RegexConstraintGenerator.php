<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\Regex;

final readonly class RegexConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaResolver $resolver,
    ) {
    }

    public function generate(Schema $schema): ?Constraint
    {
        $resolved = $this->resolver->resolved($schema);

        if ($resolved->type !== SchemaType::String || $resolved->pattern === null) {
            return null;
        }

        return new Constraint(Regex::class, ['pattern' => '/' . str_replace('/', '\\/', $resolved->pattern) . '/']);
    }
}
