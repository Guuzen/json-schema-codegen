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
        $required = [];
        $optional = [];

        foreach ($schema->properties ?? [] as $propertyName => $propertySchema) {
            if ($propertySchema->required) {
                $required[$propertyName] = $propertySchema;
                continue;
            }

            $optional[$propertyName] = $propertySchema;
        }

        return array_merge($required, $optional);
    }
}
