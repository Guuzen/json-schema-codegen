<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

final readonly class EnumLiteralType implements PhpType
{
    /**
     * @param list<string|int> $values
     */
    public function __construct(
        public array $values,
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
