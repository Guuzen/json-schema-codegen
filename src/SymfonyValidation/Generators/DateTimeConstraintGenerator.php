<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use DateTimeInterface;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ClassConstantRef;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\SchemaWalker;
use Symfony\Component\Validator\Constraints\DateTime;

final readonly class DateTimeConstraintGenerator implements ConstraintGenerator
{
    public function __construct(
        private SchemaWalker $schemaWalker,
        private string|ClassConstantRef $format = new ClassConstantRef(
            DateTimeInterface::class,
            'DateTimeInterface',
            'ATOM',
        ),
    ) {
    }

    public function generate(Schema $schema): array
    {
        return [
            ...$this->root($schema),
            ...$this->schemaWalker->anyOf($schema, $this),
            ...$this->schemaWalker->ref($schema, $this),
        ];
    }

    /**
     * @return list<Constraint>
     */
    private function root(Schema $schema): array
    {
        $constraints = [];

        if ($schema->isString() && $schema->format === 'date-time') {
            $constraints[] = new Constraint(DateTime::class, ['format' => $this->format]);
        }

        return $constraints;
    }
}
