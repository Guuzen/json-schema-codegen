<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class SchemaResolver
{
    public function __construct(
        private SchemaRegistry $registry,
    )
    {
    }

    public function build(Schema $schema, ?Schema $parent = null): ResolvedSchema
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

        return new ResolvedSchema(
            schema: $schema,
            parent: $parent,
            ref: $ref,
            items: $items,
            anyOf: $anyOf,
        );
    }
}
