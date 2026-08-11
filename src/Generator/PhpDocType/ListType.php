<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PhpDocType;

final class ListType implements PhpDocType
{
    /**
     * @param 'non-empty-list'|'list' $type
     */
    private function __construct(
        private string $type,
        private PhpDocType $element,
    )
    {
    }

    public static function nonEmptyList(PhpDocType $element): self
    {
        return new self('non-empty-list', $element);
    }

    public static function list(PhpDocType $element): self
    {
        return new self('list', $element);
    }

    public function render(): string
    {
        $element = $this->element->render();

        return match ($this->type) {
            'non-empty-list' => "non-empty-list<$element>",
            'list' => "list<$element>",
        };
    }

    public function simplify(): PhpDocType
    {
        return new self($this->type, $this->element->simplify());
    }

    public function isSupertypeOf(PhpDocType $type): bool
    {
        return false;
    }

    public function isSameTypeAs(PhpDocType $type): bool
    {
        if ($type instanceof self) {
            return $this->type === $type->type && $this->element->isSameTypeAs($type->element);
        }

        return false;
    }
}
