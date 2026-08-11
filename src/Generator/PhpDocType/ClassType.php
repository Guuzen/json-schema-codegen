<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PhpDocType;

final class ClassType implements PhpDocType
{
    public function __construct(
        private string $class,
    )
    {
    }

    public function render(): string
    {
        return $this->class;
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
        if ($type instanceof self && $this->class === $type->class) {
            return true;
        }

        return false;
    }
}
