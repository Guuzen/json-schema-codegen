<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType;

final readonly class IntType implements PhpType
{
    public function __construct(
        public ?int $min = null,
        public ?int $max = null,
    ) {
    }
}
