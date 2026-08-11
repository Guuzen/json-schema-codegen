<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PhpDocType;

final class LiteralIntType implements PhpDocType
{
    public function __construct(
        private int $literal,
    )
    {
    }

    public function render(): string
    {
        return (string)$this->literal;
    }

    public function simplify(): PhpDocType
    {
        return $this;
    }

    public function isSupertypeOf(PhpDocType $type): bool
    {
        return false;
    }

    public function isSameTypeAs(PhpDocType $type): bool
    {
        if ($type instanceof self && $type->literal === $this->literal) {
            return true;
        }

        return false;
    }
}
