<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

final class SchemaRegistry
{
    /** @var array<string, Schema> */
    private array $schemas;

    /** @param array<string, Schema> $schemas */
    public function __construct(array $schemas = [])
    {
        $this->schemas = $schemas;
    }

    public function add(AbsoluteUri $uri, Schema $schema): void
    {
        $this->schemas[$uri->value] = $schema;
    }

    public function get(AbsoluteUri $uri): Schema
    {
        return $this->schemas[$uri->value]
            ?? throw new \RuntimeException("Schema not found in registry: {$uri->value}");
    }
}
