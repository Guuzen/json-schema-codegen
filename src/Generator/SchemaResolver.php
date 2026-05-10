<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

/**
 * Follows non-object $refs so constraint generators can match on the
 * underlying schema (e.g. "string with format: uuid") without each
 * generator re-implementing ref traversal.
 *
 * Object refs are NOT followed — they represent a class boundary the
 * generator should treat as an opaque ClassRef.
 */
final readonly class SchemaResolver
{
    public function __construct(
        private SchemaRegistry $registry,
    ) {
    }

    public function resolved(Schema $schema): Schema
    {
        if ($schema->ref === null) {
            return $schema;
        }

        $referenced = $this->registry->get($schema->ref->uri);
        if ($referenced->type === SchemaType::Object) {
            return $schema;
        }

        return $this->resolved($referenced);
    }
}
