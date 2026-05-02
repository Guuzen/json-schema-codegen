<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;

interface TypeRenderer
{
    /**
     * @return list<RenderedType>
     */
    public function render(PhpType $type): array;
}
