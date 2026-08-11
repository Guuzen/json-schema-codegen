<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PhpDocType;

final class IntersectionType implements PhpDocType
{
    /**
     * @param list<PhpDocType> $types
     */
    public function __construct(
        private array $types,
    )
    {
    }

    public function simplify(): PhpDocType
    {
        return $this
            ->simplifyElements()
            ->removeDuplicates()
            ->removeSupertypes()
            ->unwrapIfSingleElement();
    }

    public function render(): string
    {
        $types = [];

        foreach ($this->types as $type) {
            $types[] = $type->render();
        }

        return implode('&', $types);
    }

    private function removeSupertypes(): self
    {
        $types = $this->types;

        $simplifiedTypes = [];

        foreach ($types as $type) {
            if (array_any($types, fn (PhpDocType $innerType) => $type->isSupertypeOf($innerType))) {
                continue;
            }

            $simplifiedTypes[] = $type;
        }

        return new self($simplifiedTypes);
    }

    private function removeDuplicates(): self
    {
        $types = $this->types;

        $simplifiedTypes = [];

        foreach ($types as $type) {
            array_shift($types);
            if (array_any($types, fn (PhpDocType $innerType) => $type->isSameTypeAs($innerType))) {
                continue;
            }

            $simplifiedTypes[] = $type;
        }

        return new self($simplifiedTypes);
    }

    private function unwrapIfSingleElement(): PhpDocType
    {
        if (count($this->types) === 1) {
            return $this->types[0];
        }

        return new self($this->types);
    }

    private function simplifyElements(): self
    {
        $simplifiedTypes = [];

        foreach ($this->types as $type) {
            $simplifiedTypes[] = $type->simplify();
        }

        return new self($simplifiedTypes);
    }

    public function isSupertypeOf(PhpDocType $type): bool
    {
        return false;
    }

    public function isSameTypeAs(PhpDocType $type): bool
    {
        return false;
    }
}
