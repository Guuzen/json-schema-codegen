<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final class ConstructorParameterOrder
{
    /**
     * @return array<string, Schema>
     */
    public function order(Schema $schema): array
    {
        return $schema->properties ?? [];
    }
}
