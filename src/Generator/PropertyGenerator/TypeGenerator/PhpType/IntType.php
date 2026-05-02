<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

final readonly class IntType implements PhpType
{
    public function __construct(
        public ?int $min = null,
        public ?int $max = null,
    ) {
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function containsClassRef(): bool
    {
        return false;
    }
}
