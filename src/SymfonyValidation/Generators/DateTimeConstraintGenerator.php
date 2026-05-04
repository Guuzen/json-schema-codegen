<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use DateTimeInterface;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ClassConstantRef;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\DateTime;

final readonly class DateTimeConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private string|ClassConstantRef $format = new ClassConstantRef(
            DateTimeInterface::class,
            'DateTimeInterface',
            'ATOM',
        ),
    ) {
    }

    public function generate(PhpType $type): ?Constraint
    {
        if (!$type instanceof StringType || $type->format !== 'date-time') {
            return null;
        }

        return new Constraint(DateTime::class, ['format' => $this->format]);
    }
}
