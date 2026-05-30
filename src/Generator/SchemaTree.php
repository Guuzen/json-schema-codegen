<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class SchemaTree
{
    /**
     * @param array<string, self>|null $properties
     * @param list<self>|null          $anyOf
     */
    public function __construct(
        public Schema $schema,
        public ?Schema $parent,
        public ?self $ref,
        public ?self $items,
        public ?array $anyOf,
        public ?array $properties,
    )
    {
    }
}
