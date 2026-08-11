<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PhpDocType;

final class StringType implements PhpDocType
{
    /**
     * @param 'non-empty-string'|'string' $type
     */
    private function __construct(
        private string $type,
    )
    {
    }

    public static function nonEmptyString(): self
    {
        return new self('non-empty-string');
    }

    public static function string(): self
    {
        return new self('string');
    }

    public function render(): string
    {
        return match ($this->type) {
            'string' => 'string',
            'non-empty-string' => 'non-empty-string',
        };
    }

    public function simplify(): PhpDocType
    {
        return $this;
    }

    public function isSupertypeOf(PhpDocType $type): bool
    {
        if ($type instanceof LiteralStringType) {
            return true;
        }

        return false;
    }

    public function isSameTypeAs(PhpDocType $type): bool
    {
        if ($type instanceof self) {
            return $this->type === $type->type;
        }

        return false;
    }
}
