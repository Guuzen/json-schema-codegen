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

    /**
     * Returns the single non-null/non-undefined type, or null if there are multiple such types.
     *
     * @template Type of PhpType
     *
     * @param class-string<Type> $class
     *
     * @return ?Type
     */
    public function findOnlySingleType(string $class): ?PhpType
    {
        $concrete = array_values(array_filter(
            $this->types,
            fn($type) => !$type instanceof NullType && !$type instanceof UndefinedType,
        ));

        $filtered = array_filter($concrete, fn($type) => $type instanceof $class);

        if (count($filtered) !== count($concrete)) {
            return null;
        }

        if (count($concrete) > 1) {
            return null;
        }

        return $concrete[0] instanceof $class ? $concrete[0] : null;
    }
}
