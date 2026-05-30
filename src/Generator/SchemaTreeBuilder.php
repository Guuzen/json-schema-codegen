<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class SchemaTreeBuilder
{
    public function __construct(
        private SchemaRegistry $registry,
    )
    {
    }

    public function build(Schema $schema, ?Schema $parent = null): SchemaTree
    {
        $ref = null;
        if ($schema->ref !== null) {
            $ref = $this->build(
                $this->registry->get($schema->ref->uri),
                $schema,
            );
        }

        $items = null;
        if ($schema->items !== null) {
            $items = $this->build($schema->items);
        }

        $anyOf = null;
        if ($schema->anyOf !== null) {
            foreach ($schema->anyOf as $branch) {
                $anyOf[] = $this->build($branch);
            }
        }

        // Properties are only expanded for schemas not reached through a $ref:
        // a ref target is a separate DTO referenced by class name, so inlining its
        // properties would be both unused and a source of infinite recursion on
        // self-referential schemas.
        $properties = null;
        if ($parent === null && $schema->properties !== null) {
            foreach ($schema->properties as $name => $propertySchema) {
                $properties[$name] = $this->build($propertySchema);
            }
        }

        return new SchemaTree(
            schema: $schema,
            parent: $parent,
            ref: $ref,
            items: $items,
            anyOf: $anyOf,
            properties: $properties,
        );
    }
}
