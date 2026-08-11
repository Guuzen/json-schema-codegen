<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PhpDocType;

final class OtherScalarType implements PhpDocType
{
    /**
     * @param 'float'|'bool'|'object'|'null'|'mixed' $type
     */
    private function __construct(
        private string $type,
    )
    {
    }

    public static function float(): self
    {
        return new self('float');
    }

    public static function bool(): self
    {
        return new self('bool');
    }

    public static function object(): self
    {
        return new self('object');
    }

    public static function null(): self
    {
        return new self('null');
    }

    public static function mixed(): self
    {
        return new self('mixed');
    }

    public function render(): string
    {
        return match ($this->type) {
            'float' => 'float',
            'bool' => 'bool',
            'object' => 'object',
            'null' => 'null',
            'mixed' => 'mixed',
        };
    }

    public function simplify(): PhpDocType
    {
        return $this;
    }

    public function isSupertypeOf(PhpDocType $type): bool
    {
        if ($type instanceof self) {
            return $this->type === 'mixed' && $type->type !== 'mixed';
        }

        return true;
    }

    public function isSameTypeAs(PhpDocType $type): bool
    {
        if ($type instanceof self) {
            return $this->type === $type->type;
        }

        return false;
    }
}
