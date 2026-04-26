<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType;

final readonly class UnionType implements PhpType
{
    /**
     * @param list<PhpType> $types
     */
    public function __construct(
        public array $types,
    ) {
    }
}
