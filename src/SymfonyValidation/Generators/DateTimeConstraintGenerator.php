<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use DateTimeInterface;
use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ClassConstantRef;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\DateTime;

final readonly class DateTimeConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaResolver $resolver,
        private string|ClassConstantRef $format = new ClassConstantRef(
            DateTimeInterface::class,
            'DateTimeInterface',
            'ATOM',
        ),
    ) {
    }

    public function generate(Schema $schema): ?Constraint
    {
        $resolved = $this->resolver->resolved($schema);

        if ($resolved->type !== SchemaType::String || $resolved->format !== 'date-time') {
            return null;
        }

        return new Constraint(DateTime::class, ['format' => $this->format]);
    }
}
