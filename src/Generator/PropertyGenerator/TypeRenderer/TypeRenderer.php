<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

interface TypeRenderer
{
    /**
     * @return list<RenderedType>
     */
    public function render(Schema $schema): array;
}
