<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Generator\ResolvedSchema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class DefaultAnnotationGenerator implements AnnotationGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
        private SchemaResolver $schemaBuilder,
    )
    {
    }

    public function generate(Schema $schema): ResolvedAnnotation
    {
        $resolvedSchema = $this->schemaBuilder->build($schema);

        return $this->render($resolvedSchema);
    }

    private function render(?ResolvedSchema $schema): ResolvedAnnotation
    {
        if ($schema === null) {
            return ResolvedAnnotation::mixed();
        }

        $allAnnotations = [];

        $allAnnotations[] = $this->render($schema->ref);
        $allAnnotations[] = $this->renderAnyOf($schema);
        $allAnnotations[] = $this->renderEnum($schema);
        $allAnnotations[] = $this->renderTypes($schema);

        if (array_all($allAnnotations, fn(ResolvedAnnotation $annotation) => $annotation->annotation === 'mixed')) {
            return ResolvedAnnotation::mixed();
        }

        $hasMixed = array_any(
            $allAnnotations,
            fn(ResolvedAnnotation $annotation) => $annotation->annotation === 'mixed',
        );
        $hasNotMixed = array_any(
            $allAnnotations,
            fn(ResolvedAnnotation $annotation) => $annotation->annotation !== 'mixed',
        );

        if ($hasMixed && $hasNotMixed) {
            $allAnnotations = array_filter(
                $allAnnotations,
                fn(ResolvedAnnotation $annotation) => $annotation->annotation !== 'mixed',
            );
        }

        if ($allAnnotations !== []) {
            return array_shift($allAnnotations)->intersect($allAnnotations);
        }

        return ResolvedAnnotation::mixed();
    }

    private function renderAnyOf(ResolvedSchema $schema): ResolvedAnnotation
    {
        if ($schema->anyOf === null) {
            return ResolvedAnnotation::mixed();
        }

        $annotations = [];
        foreach ($schema->anyOf as $anyOfSchema) {
            $annotations[] = $this->render($anyOfSchema);
        }

        if ($annotations === []) {
            return ResolvedAnnotation::mixed();
        }

        return array_shift($annotations)->unite($annotations);
    }

    private function renderEnum(ResolvedSchema $schema): ResolvedAnnotation
    {
        if ($schema->schema->enum === [] || $schema->schema->enum === null) {
            return ResolvedAnnotation::mixed();
        }

        $annotation = implode(
            '|', array_map(
                fn(string|int $v) => is_string($v) ? "'{$v}'" : (string)$v,
                $schema->schema->enum,
            )
        );

        return new ResolvedAnnotation($annotation, []);
    }

    private function renderTypes(ResolvedSchema $schema): ResolvedAnnotation
    {
        $annotations = [];

        $types = is_array($schema->schema->type) ? $schema->schema->type : [$schema->schema->type];

        foreach ($types as $type) {
            $typeAnnotation = match ($type) {
                SchemaType::Integer => $this->renderInt($schema->schema->minimum, $schema->schema->maximum),
                SchemaType::String => $this->renderString($schema),
                SchemaType::Number => new ResolvedAnnotation('float', []),
                SchemaType::Boolean => new ResolvedAnnotation('bool', []),
                SchemaType::Object => $this->renderObject($schema->parent?->ref, $schema->parent?->xAlias),
                SchemaType::Null => new ResolvedAnnotation('null', []),
                SchemaType::Array => $this->renderList($schema->items, $schema->schema->minItems),
                default => new ResolvedAnnotation('mixed', []),
            };

            $annotations[] = $typeAnnotation;
        }

        if ($annotations === []) {
            return ResolvedAnnotation::mixed();
        }

        return array_shift($annotations)->unite($annotations);
    }

    private function renderObject(?Ref $ref, ?string $xAlias): ResolvedAnnotation
    {
        if ($ref?->uri === null) {
            return new ResolvedAnnotation('object', []);
        }

        $fqcn = $this->fqcnResolver->fromUri($ref->uri);
        $alias = $xAlias ?? $fqcn->className();

        return new ResolvedAnnotation($alias, [['alias' => $alias, 'fqcn' => $fqcn->fqcn]]);
    }

    private function renderInt(?int $minimum, ?int $maximum): ResolvedAnnotation
    {
        if ($minimum === null && $maximum === null) {
            return new ResolvedAnnotation('int', []);
        }

        $min = $minimum !== null ? (string)$minimum : 'min';
        $max = $maximum !== null ? (string)$maximum : 'max';

        return new ResolvedAnnotation("int<{$min}, {$max}>", []);
    }

    private function renderString(ResolvedSchema $schema): ResolvedAnnotation
    {
        if ($schema->schema->minLength !== null && $schema->schema->minLength >= 1) {
            $annoatation = 'non-empty-string';
        } else {
            $annoatation = 'string';
        }

        return new ResolvedAnnotation($annoatation, []);
    }

    private function renderList(?ResolvedSchema $items, ?int $minItems): ResolvedAnnotation
    {
        $itemAnnotation = $this->render($items);
        $prefix = $minItems !== null && $minItems >= 1 ? 'non-empty-list' : 'list';

        return new ResolvedAnnotation("{$prefix}<{$itemAnnotation->annotation}>", $itemAnnotation->imports);
    }
}
