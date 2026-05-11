<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Schema;

use Guuzen\JsonSchemaCodegen\Schema\Keyword\DefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;

/**
 * Represents a JSON Schema object.
 * The structure mirrors the JSON Schema draft — a schema can recursively contain other schemas.
 *
 * @see https://json-schema.org/draft/2020-12
 */
final readonly class Schema
{
    /**
     * @param SchemaType|list<SchemaType>|null $type
     * @param array<string, Schema>|null       $properties
     * @param list<Schema>|null                $oneOf
     * @param list<string|int>|null            $enum
     */
    public function __construct(
        public SchemaType|array|null $type = null,
        public bool $required = true,
        public ?array $properties = null,
        public ?Schema $items = null,
        public ?Ref $ref = null,
        public ?array $oneOf = null,
        public ?array $enum = null,
        public ?string $title = null,
        public ?string $xAlias = null,
        public ?string $description = null,
        public ?int $minimum = null,
        public ?int $maximum = null,
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?int $minItems = null,
        public ?int $maxItems = null,
        public ?DefaultValue $default = null,
        public ?string $format = null,
        public ?string $pattern = null,
    )
    {
    }

    public function isInteger(): bool
    {
        if ($this->type === SchemaType::Integer) {
            return true;
        }

        if (is_array($this->type) && in_array(SchemaType::Integer, $this->type, true)) {
            return true;
        }

        return false;
    }

    public function isString(): bool
    {
        if ($this->type === SchemaType::String) {
            return true;
        }

        if (is_array($this->type) && in_array(SchemaType::String, $this->type, true)) {
            return true;
        }

        return false;
    }

    public function isArray(): bool
    {
        if ($this->type === SchemaType::Array) {
            return true;
        }

        if (is_array($this->type) && in_array(SchemaType::Array, $this->type, true)) {
            return true;
        }

        return false;
    }

    public function isObject(): bool
    {
        if ($this->type === SchemaType::Object) {
            return true;
        }

        if (is_array($this->type) && in_array(SchemaType::Object, $this->type, true)) {
            return true;
        }

        return false;
    }
}
