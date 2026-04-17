<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Schema;

use Guuzen\JsonSchemaCodegen\Generator\SchemaDecoder;

final class JsonDecoder implements SchemaDecoder
{
    /**
     * @return array<string, mixed>
     */
    public function decode(string $content): array
    {
        $schema = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($schema)) {
            throw new \InvalidArgumentException('Invalid JSON schema: unable to decode JSON');
        }

        if (!self::assertSchema($schema)) {
            throw new \InvalidArgumentException('Invalid JSON schema: unable to decode JSON');
        }

        return $schema;
    }

    /**
     * @param array<mixed, mixed> $schema
     *
     * @phpstan-assert-if-true array<string, mixed> $schema
     */
    private static function assertSchema(array $schema): bool
    {
        return array_all($schema, fn($item, $key) => is_string($key));
    }
}
