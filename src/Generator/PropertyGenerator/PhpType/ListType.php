<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType;

final readonly class ListType implements PhpType
{
    public function __construct(
        public PhpType $itemType,
        public bool    $nonEmpty = false,
    ) {
    }
}
