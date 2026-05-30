<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\Fqcn;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\RelativeUnixPath;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

/**
 * Maps schema URIs to existing types that should be referenced instead of generated.
 *
 * A schema listed here is never turned into a class: references to it render as the
 * mapped type (e.g. {@see \DateTimeImmutable}), and no file is produced for it.
 */
final readonly class TypeMappings
{
    /**
     * @param array<string, Fqcn> $mappings keyed by URI value
     */
    public function __construct(
        private array $mappings = [],
    )
    {
    }

    /**
     * @param array<string, class-string> $mappings relative schema path => fully-qualified type name
     */
    public static function create(AbsoluteUnixDirectoryPath $schemaPath, array $mappings): self
    {
        $resolved = [];
        foreach ($mappings as $relativePath => $fqcn) {
            $uri = $schemaPath->resolve(new RelativeUnixPath($relativePath))->toUri();
            $resolved[$uri->value] = new Fqcn($fqcn);
        }

        return new self($resolved);
    }

    public function get(AbsoluteUri $uri): ?Fqcn
    {
        return $this->mappings[$uri->value] ?? null;
    }

    public function has(AbsoluteUri $uri): bool
    {
        return isset($this->mappings[$uri->value]);
    }
}
