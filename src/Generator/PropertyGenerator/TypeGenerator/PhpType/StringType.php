<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

final readonly class StringType implements PhpType
{
    public function __construct(
        public bool $nonEmpty = false,
        public ?string $format = null,
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
