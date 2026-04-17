<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Schema;

use Guuzen\JsonSchemaCodegen\Generator\SchemaDecoder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class YamlDecoder implements SchemaDecoder
{
    /**
     * @return array<string, mixed>
     */
    public function decode(string $content): array
    {
        try {
            $schema = Yaml::parse($content);
        } catch (ParseException $e) {
            throw new \InvalidArgumentException('Invalid YAML schema: ' . $e->getMessage(), previous: $e);
        }

        if (!is_array($schema)) {
            throw new \InvalidArgumentException('Invalid YAML schema: root must be a mapping');
        }

        if (!self::assertSchema($schema)) {
            throw new \InvalidArgumentException('Invalid YAML schema: root must be a mapping');
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
