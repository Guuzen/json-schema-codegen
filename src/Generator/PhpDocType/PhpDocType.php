<?php
declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PhpDocType;

interface PhpDocType
{
    public function simplify(): self;

    public function isSupertypeOf(self $type): bool;

    public function isSameTypeAs(self $type): bool;

    public function render(): string;
}
