<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Symfony\Component\Validator\Constraints\Regex;

final readonly class RegexConstraintGenerator implements ConstraintGenerator
{
    public function generate(PhpType $type): ?Constraint
    {
        if (!$type instanceof StringType || $type->pattern === null) {
            return null;
        }

        return new Constraint(Regex::class, ['pattern' => '/' . str_replace('/', '\\/', $type->pattern) . '/']);
    }
}
