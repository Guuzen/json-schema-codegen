<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

final readonly class UnionType implements PhpType
{
    /**
     * @param list<PhpType> $types
     */
    public function __construct(
        public array $types,
    ) {
    }

    public function isNullable(): bool
    {
        return array_any($this->types, fn($type) => $type instanceof NullType);
    }

    public function containsClassRef(): bool
    {
        return array_any($this->types, fn($type) => $type->containsClassRef());
    }
}
