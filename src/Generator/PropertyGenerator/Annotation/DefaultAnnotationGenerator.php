<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class DefaultAnnotationGenerator implements AnnotationGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
    )
    {
    }

    public function generate(Schema $schema): ResolvedAnnotation
    {
        return $this->render($schema);
    }

    private function render(?Schema $schema): ResolvedAnnotation
    {
        if ($schema === null) {
            return ResolvedAnnotation::mixed();
        }

        $allAnnotations = [];

        $allAnnotations[] = $this->renderRef($schema);
        $allAnnotations[] = $this->renderAnyOf($schema);
        $allAnnotations[] = $this->renderEnum($schema);
        $allAnnotations[] = $this->renderTypes($schema);

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

    private function renderAnyOf(Schema $schema): ResolvedAnnotation
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

        return ResolvedAnnotation::unite($annotations);
    }

    private function renderEnum(Schema $schema): ResolvedAnnotation
    {
        if ($schema->enum === [] || $schema->enum === null) {
            return ResolvedAnnotation::mixed();
        }

        $annotation = implode(
            '|', array_map(
                fn(string|int $v) => is_string($v) ? "'{$v}'" : (string)$v,
                $schema->enum,
            )
        );

        return new ResolvedAnnotation($annotation, []);
    }

    private function renderTypes(Schema $schema): ResolvedAnnotation
    {
        $annotations = [];

        $types = is_array($schema->type) ? $schema->type : [$schema->type];

        foreach ($types as $type) {
            $typeAnnotation = match ($type) {
                SchemaType::Integer => $this->renderInt($schema->minimum, $schema->maximum),
                SchemaType::String => $this->renderString($schema),
                SchemaType::Number => new ResolvedAnnotation('float', []),
                SchemaType::Boolean => new ResolvedAnnotation('bool', []),
                SchemaType::Object => new ResolvedAnnotation('object', []),
                SchemaType::Null => new ResolvedAnnotation('null', []),
                SchemaType::Array => $this->renderList($schema->items, $schema->minItems),
                default => new ResolvedAnnotation('mixed', []),
            };

            $annotations[] = $typeAnnotation;
        }

        if ($annotations === []) {
            return ResolvedAnnotation::mixed();
        }

        return ResolvedAnnotation::unite($annotations);
    }

    private function renderRef(Schema $schema): ResolvedAnnotation
    {
        $ref = $schema->ref;
        if ($ref === null) {
            return ResolvedAnnotation::mixed();
        }

        $fqcn = $this->fqcnResolver->fromUri($ref->uri);
        $alias = $schema->xAlias ?? $fqcn->className();

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

    private function renderString(Schema $schema): ResolvedAnnotation
    {
        if ($schema->minLength !== null && $schema->minLength >= 1) {
            $annoatation = 'non-empty-string';
        } else {
            $annoatation = 'string';
        }

        return new ResolvedAnnotation($annoatation, []);
    }

    private function renderList(?Schema $items, ?int $minItems): ResolvedAnnotation
    {
        $itemAnnotation = $this->render($items);
        $prefix = $minItems !== null && $minItems >= 1 ? 'non-empty-list' : 'list';

        return new ResolvedAnnotation("{$prefix}<{$itemAnnotation->annotation}>", $itemAnnotation->imports);
    }
}
