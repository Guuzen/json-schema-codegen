<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final class ConstructorParameterOrder
{
    /**
     * Returns properties sorted so PHP parameters with default values come last.
     *
     * @return array<string, Schema>
     */
    public function order(Schema $schema): array
    {
        $withoutDefault = [];
        $withDefault = [];

        foreach ($schema->properties ?? [] as $name => $propertySchema) {
            if ($propertySchema->default !== null) {
                $withDefault[$name] = $propertySchema;
            } else {
                $withoutDefault[$name] = $propertySchema;
            }
        }

        return [...$withoutDefault, ...$withDefault];
    }
}
