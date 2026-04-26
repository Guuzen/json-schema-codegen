<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType;

final readonly class StringType implements PhpType
{
    public function __construct(
        public bool $nonEmpty = false,
    ) {
    }
}
