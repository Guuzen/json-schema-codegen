<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class ResolvedSchema
{
    /**
     * @param list<self> $anyOf
     */
    public function __construct(
        public Schema $schema,
        public ?Schema $parent,
        public ?self $ref,
        public ?self $items,
        public ?array $anyOf,
    )
    {
    }
}
