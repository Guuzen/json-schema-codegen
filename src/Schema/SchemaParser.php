<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Schema;

use Guuzen\JsonSchemaCodegen\Schema\Keyword\DefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Guuzen\JsonSchemaCodegen\Uri\RefUriResolver;

final readonly class SchemaParser
{
    public function __construct(
        private RefUriResolver $refUriResolver = new RefUriResolver(),
    ) {
    }

    /**
     * @param array<string, mixed> $schema
     */
    public function parse(array $schema, AbsoluteUri $schemaUri): Schema
    {
        return $this->parseSchemaNode($schema, $schemaUri);
    }

    /**
     * @param array<string, mixed> $node
     */
    private function parseSchemaNode(array $node, AbsoluteUri $schemaUri): Schema
    {
        /** @var string|list<string>|null $rawType */
        $rawType = $node['type'] ?? null;
        $type = is_array($rawType)
            ? array_map(static fn(string $t) => SchemaType::from($t), $rawType)
            : ($rawType !== null ? SchemaType::from($rawType) : null);

        $ref = null;
        /** @var string|null $rawRef */
        $rawRef = $node['$ref'] ?? null;
        if ($rawRef !== null) {
            $ref = new Ref($this->refUriResolver->resolve($rawRef, $schemaUri));
        }

        $items = null;
        if (isset($node['items']) && is_array($node['items'])) {
            /** @var array<string, mixed> $rawItems */
            $rawItems = $node['items'];
            $items = $this->parseSchemaNode($rawItems, $schemaUri);
        }

        $properties = null;
        if (isset($node['properties']) && is_array($node['properties'])) {
            /** @var array<string, array<string, mixed>> $rawProperties */
            $rawProperties = $node['properties'];
            $properties = [];
            foreach ($rawProperties as $name => $propertySchema) {
                $properties[$name] = $this->parseSchemaNode($propertySchema, $schemaUri);
            }
        }

        $oneOf = null;
        if (isset($node['oneOf']) && is_array($node['oneOf'])) {
            /** @var list<array<string, mixed>> $rawOneOf */
            $rawOneOf = $node['oneOf'];
            $oneOf = array_map(
                fn(array $branch) => $this->parseSchemaNode($branch, $schemaUri),
                $rawOneOf,
            );
        }

        /** @var list<string|int>|null $enum */
        $enum = $node['enum'] ?? null;

        /** @var string|null $title */
        $title = isset($node['title']) && is_string($node['title']) ? $node['title'] : null;

        /** @var string|null $description */
        $description = $node['description'] ?? null;

        /** @var int|null $minimum */
        $minimum = isset($node['minimum']) && is_int($node['minimum']) ? $node['minimum'] : null;

        /** @var int|null $maximum */
        $maximum = isset($node['maximum']) && is_int($node['maximum']) ? $node['maximum'] : null;

        /** @var int|null $minLength */
        $minLength = isset($node['minLength']) && is_int($node['minLength']) ? $node['minLength'] : null;

        /** @var int|null $maxLength */
        $maxLength = isset($node['maxLength']) && is_int($node['maxLength']) ? $node['maxLength'] : null;

        /** @var int|null $minItems */
        $minItems = isset($node['minItems']) && is_int($node['minItems']) ? $node['minItems'] : null;

        /** @var int|null $maxItems */
        $maxItems = isset($node['maxItems']) && is_int($node['maxItems']) ? $node['maxItems'] : null;

        $default = array_key_exists('default', $node) ? new DefaultValue($node['default']) : null;

        return new Schema(
            type: $type,
            properties: $properties,
            items: $items,
            ref: $ref,
            oneOf: $oneOf,
            enum: $enum,
            title: $title,
            description: $description,
            minimum: $minimum,
            maximum: $maximum,
            minLength: $minLength,
            maxLength: $maxLength,
            minItems: $minItems,
            maxItems: $maxItems,
            default: $default,
        );
    }
}
