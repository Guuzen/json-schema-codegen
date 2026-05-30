<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\SchemaTree;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;

final readonly class DefaultAnnotationGenerator implements AnnotationGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
    )
    {
    }

    public function generate(SchemaTree $tree): ResolvedAnnotation
    {
        return $this->render($tree);
    }

    private function render(?SchemaTree $tree): ResolvedAnnotation
    {
        if ($tree === null) {
            return ResolvedAnnotation::mixed();
        }

        $allAnnotations = [];

        $allAnnotations[] = $this->render($tree->ref);
        $allAnnotations[] = $this->renderAnyOf($tree);
        $allAnnotations[] = $this->renderEnum($tree);
        $allAnnotations[] = $this->renderTypes($tree);

        if (array_all($allAnnotations, fn(ResolvedAnnotation $annotation) => $annotation->isMixed())) {
            return ResolvedAnnotation::mixed();
        }

        $hasMixed = array_any(
            $allAnnotations,
            fn(ResolvedAnnotation $annotation) => $annotation->isMixed(),
        );
        $hasNotMixed = array_any(
            $allAnnotations,
            fn(ResolvedAnnotation $annotation) => $annotation->isNotMixed(),
        );

        if ($hasMixed && $hasNotMixed) {
            $allAnnotations = array_filter(
                $allAnnotations,
                fn(ResolvedAnnotation $annotation) => $annotation->isNotMixed(),
            );
        }

        if ($allAnnotations !== []) {
            return ResolvedAnnotation::intersect($allAnnotations);
        }

        return ResolvedAnnotation::mixed();
    }

    private function renderAnyOf(SchemaTree $tree): ResolvedAnnotation
    {
        if ($tree->anyOf === null) {
            return ResolvedAnnotation::mixed();
        }

        $annotations = [];
        foreach ($tree->anyOf as $anyOfSchema) {
            $annotations[] = $this->render($anyOfSchema);
        }

        if ($annotations === []) {
            return ResolvedAnnotation::mixed();
        }

        return ResolvedAnnotation::unite($annotations);
    }

    private function renderEnum(SchemaTree $tree): ResolvedAnnotation
    {
        if ($tree->schema->enum === [] || $tree->schema->enum === null) {
            return ResolvedAnnotation::mixed();
        }

        $annotation = implode(
            '|', array_map(
                fn(string|int $v) => is_string($v) ? "'{$v}'" : (string)$v,
                $tree->schema->enum,
            )
        );

        return new ResolvedAnnotation($annotation, []);
    }

    private function renderTypes(SchemaTree $tree): ResolvedAnnotation
    {
        $annotations = [];

        $types = is_array($tree->schema->type) ? $tree->schema->type : [$tree->schema->type];

        foreach ($types as $type) {
            $typeAnnotation = match ($type) {
                SchemaType::Integer => $this->renderInt($tree->schema->minimum, $tree->schema->maximum),
                SchemaType::String => $this->renderString($tree),
                SchemaType::Number => new ResolvedAnnotation('float', []),
                SchemaType::Boolean => new ResolvedAnnotation('bool', []),
                SchemaType::Object => $this->renderObject($tree->parent?->ref, $tree->parent?->xAlias),
                SchemaType::Null => new ResolvedAnnotation('null', []),
                SchemaType::Array => $this->renderList($tree->items, $tree->schema->minItems),
                default => new ResolvedAnnotation('mixed', []),
            };

            $annotations[] = $typeAnnotation;
        }

        if ($annotations === []) {
            return ResolvedAnnotation::mixed();
        }

        return ResolvedAnnotation::unite($annotations);
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

    private function renderString(SchemaTree $tree): ResolvedAnnotation
    {
        if ($tree->schema->minLength !== null && $tree->schema->minLength >= 1) {
            $annoatation = 'non-empty-string';
        } else {
            $annoatation = 'string';
        }

        return new ResolvedAnnotation($annoatation, []);
    }

    private function renderList(?SchemaTree $items, ?int $minItems): ResolvedAnnotation
    {
        $itemAnnotation = $this->render($items);
        $prefix = $minItems !== null && $minItems >= 1 ? 'non-empty-list' : 'list';

        return new ResolvedAnnotation("{$prefix}<{$itemAnnotation->annotation}>", $itemAnnotation->imports);
    }
}
