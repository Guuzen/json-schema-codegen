<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

final readonly class ListType implements PhpType
{
    public function __construct(
        public PhpType $itemType,
        public bool    $nonEmpty = false,
    ) {
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function containsClassRef(): bool
    {
        return $this->itemType->containsClassRef();
    }
}
