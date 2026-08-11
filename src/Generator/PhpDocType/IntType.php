<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PhpDocType;

final class IntType implements PhpDocType
{
    public function __construct(
        private ?int $lowerBound,
        private ?int $upperBound,
    )
    {
    }

    public function render(): string
    {
        if ($this->lowerBound === null && $this->upperBound === null) {
            return 'int';
        }

        $lowerBound = $this->lowerBound === null ? 'min' : (string)$this->lowerBound;
        $upperBound = $this->upperBound === null ? 'max' : (string)$this->upperBound;

        return "int<$lowerBound, $upperBound>";
    }

    public function simplify(): PhpDocType
    {
        return $this;
    }

    public function isSupertypeOf(PhpDocType $type): bool
    {
        if ($type instanceof self) {
            return $this->lowerBound < $type->lowerBound && $this->upperBound > $type->lowerBound;
        }

        if ($type instanceof LiteralIntType) {
            return true;
        }

        return false;
    }

    public function isSameTypeAs(PhpDocType $type): bool
    {
        if ($type instanceof self) {
            return $this->lowerBound === $type->lowerBound && $this->upperBound === $type->upperBound;
        }

        return false;
    }
}
