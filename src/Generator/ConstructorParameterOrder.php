<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

final class ConstructorParameterOrder
{
    /**
     * @return array<string, SchemaTree>
     */
    public function order(SchemaTree $tree): array
    {
        $required = [];
        $optional = [];

        foreach ($tree->properties ?? [] as $propertyName => $propertySchema) {
            if ($propertySchema->schema->required) {
                $required[$propertyName] = $propertySchema;
                continue;
            }

            $optional[$propertyName] = $propertySchema;
        }

        return array_merge($required, $optional);
    }
}
