<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Undefined;

/**
 * @implements PropertyGenerator<ResolvedTypes>
 */
final readonly class TypeGenerator implements PropertyGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
        private SchemaRegistry $registry,
    ) {
    }

    /**
     */
    public function generate(Schema $schema): ResolvedTypes
    {
        if ($schema->ref !== null) {
            $referencedSchema = $this->registry->get($schema->ref->uri);

            if ($referencedSchema->type === SchemaType::Object) {
                $fqcn = $this->fqcnResolver->fromUri($schema->ref->uri);
                $alias = $schema->title ?? $fqcn->className();

                return $this->withUndefinedIfNeeded(new ResolvedTypes(
                    [$alias],
                    [['alias' => $alias, 'fqcn' => $fqcn->fqcn]],
                ), $schema);
            }

            return $this->withUndefinedIfNeeded($this->generate($referencedSchema), $schema);
        }

        if ($schema->oneOf !== null) {
            return $this->withUndefinedIfNeeded($this->mergeResolvedTypes(array_map(
                fn(Schema $branch): ResolvedTypes => $branch->type === SchemaType::Null
                    ? new ResolvedTypes([], [])
                    : $this->generate($branch),
                $schema->oneOf,
            )), $schema);
        }

        if (is_array($schema->type)) {
            return $this->withUndefinedIfNeeded($this->mergeResolvedTypes(array_map(
                fn(SchemaType $type): ResolvedTypes => $type === SchemaType::Null
                    ? new ResolvedTypes([], [])
                    : $this->generate(new Schema(type: $type)),
                $schema->type,
            )), $schema);
        }

        return $this->withUndefinedIfNeeded(match ($schema->type) {
            SchemaType::String => new ResolvedTypes(['string'], []),
            SchemaType::Integer => new ResolvedTypes(['integer'], []),
            SchemaType::Number => new ResolvedTypes(['float'], []),
            SchemaType::Boolean => new ResolvedTypes(['bool'], []),
            SchemaType::Array => new ResolvedTypes(['array'], []),
            SchemaType::Object => new ResolvedTypes(['object'], []),
            default => new ResolvedTypes([], []),
        }, $schema);
    }

    /**
     * @param list<ResolvedTypes> $resolvedTypes
     */
    private function mergeResolvedTypes(array $resolvedTypes): ResolvedTypes
    {
        $typesByKey = [];
        $importsByKey = [];

        foreach ($resolvedTypes as $resolvedType) {
            foreach ($resolvedType->types as $type) {
                $typesByKey[$type] = $type;
            }

            foreach ($resolvedType->imports as $import) {
                $importsByKey[$import['alias'] . ':' . $import['fqcn']] = $import;
            }
        }

        return new ResolvedTypes(array_values($typesByKey), array_values($importsByKey));
    }

    private function withUndefinedIfNeeded(ResolvedTypes $resolvedTypes, Schema $schema): ResolvedTypes
    {
        if (!$schema->required && $schema->default === null) {
            return $this->mergeResolvedTypes([
                $resolvedTypes,
                new ResolvedTypes(['Undefined'], [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
            ]);
        }

        return $resolvedTypes;
    }
}
