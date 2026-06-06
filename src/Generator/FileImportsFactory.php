<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\Fqcn;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

/**
 * Builds the {@see FileImports} for one generated file: the file's own class plus every
 * type referenced by a $ref anywhere in its schema, resolved the same way the renderer
 * resolves them so that the import decisions and the rendered names always agree.
 */
final readonly class FileImportsFactory
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
        private TypeMappings $typeMappings,
    )
    {
    }

    public function forFile(AbsoluteUri $schemaUri, Schema $schema): FileImports
    {
        return new FileImports(
            fqcn: $this->fqcnResolver->fromUri($schemaUri),
            imports: $this->imports($schemaUri, $schema),
        );
    }

    /**
     * @return array<string, Fqcn>
     */
    private function imports(AbsoluteUri $uri, Schema $schema): array
    {
        $own = $this->fqcnResolver->fromUri($uri);
        $fqcns = $this->gatherFqcns($schema);

        // The file's own class already claims its short name.
        $shortNameCounts = [$own->className() => 1];

        $distinct = [];
        foreach ($fqcns as $fqcn) {
            if (isset($distinct[$fqcn->fqcn])) {
                continue;
            }
            $distinct[$fqcn->fqcn] = $fqcn;
            $shortNameCounts[$fqcn->className()] = ($shortNameCounts[$fqcn->className()] ?? 0) + 1;
        }

        $imported = [];
        foreach ($distinct as $fqcn) {
            if ($shortNameCounts[$fqcn->className()] === 1) {
                $imported[$fqcn->fqcn] = $fqcn;
            }
        }

        return $imported;
    }

    /**
     * @return list<Fqcn>
     */
    private function gatherFqcns(Schema $schema): array
    {
        $fqcns = [];

        if ($schema->ref !== null) {
            $uri = $schema->ref->uri;
            $fqcns[] = $this->typeMappings->get($uri) ?? $this->fqcnResolver->fromUri($uri);
        }

        if ($schema->items !== null) {
            $fqcns = [...$fqcns, ...$this->gatherFqcns($schema->items)];
        }

        foreach ($schema->anyOf ?? [] as $branch) {
            $fqcns = [...$fqcns, ...$this->gatherFqcns($branch)];
        }

        foreach ($schema->properties ?? [] as $property) {
            $fqcns = [...$fqcns, ...$this->gatherFqcns($property)];
        }

        return $fqcns;
    }
}
