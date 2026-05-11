<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Undefined;

final readonly class DefaultAnnotationGenerator implements AnnotationGenerator
{
    public function __construct(
        private FqcnResolver   $fqcnResolver,
        private SchemaRegistry $registry,
    ) {
    }

    public function generate(Schema $schema): ResolvedAnnotation
    {
        $rendered = $this->renderRequired($schema);

        if ($schema->required || $schema->default !== null || $this->isMixed($schema)) {
            return $rendered;
        }

        return new ResolvedAnnotation(
            $rendered->annotation . '|Undefined',
            [...$rendered->imports, ['alias' => 'Undefined', 'fqcn' => Undefined::class]],
        );
    }

    private function renderRequired(Schema $schema): ResolvedAnnotation
    {
        if ($schema->ref !== null) {
            $referenced = $this->registry->get($schema->ref->uri);

            if ($referenced->type === SchemaType::Object) {
                $fqcn  = $this->fqcnResolver->fromUri($schema->ref->uri);
                $alias = $schema->xAlias ?? $fqcn->className();
                return new ResolvedAnnotation($alias, [['alias' => $alias, 'fqcn' => $fqcn->fqcn]]);
            }

            return $this->renderRequired($referenced);
        }

        if ($schema->anyOf !== null) {
            return $this->renderBranches($schema->anyOf);
        }

        if (is_array($schema->type)) {
            return $this->renderBranches(array_map(
                fn(SchemaType $t) => new Schema(type: $t),
                $schema->type,
            ));
        }

        if ($schema->enum !== null) {
            return new ResolvedAnnotation($this->renderEnum($schema->enum), []);
        }

        return match ($schema->type) {
            SchemaType::String  => new ResolvedAnnotation(
                $schema->minLength !== null && $schema->minLength >= 1 ? 'non-empty-string' : 'string',
                [],
            ),
            SchemaType::Integer => new ResolvedAnnotation($this->renderInt($schema), []),
            SchemaType::Number  => new ResolvedAnnotation('float', []),
            SchemaType::Boolean => new ResolvedAnnotation('bool', []),
            SchemaType::Null    => new ResolvedAnnotation('null', []),
            SchemaType::Object  => new ResolvedAnnotation('object', []),
            SchemaType::Array   => $this->renderList($schema),
            default             => new ResolvedAnnotation('mixed', []),
        };
    }

    /**
     * @param list<Schema> $branches
     */
    private function renderBranches(array $branches): ResolvedAnnotation
    {
        $nonNull = [];
        $hasNull = false;
        foreach ($branches as $branch) {
            if ($branch->type === SchemaType::Null) {
                $hasNull = true;
                continue;
            }
            $nonNull[] = $branch;
        }

        $annotations = [];
        $imports = [];
        foreach ($nonNull as $branch) {
            $rendered = $this->renderRequired($branch);
            $annotations[] = $rendered->annotation;
            $imports = [...$imports, ...$rendered->imports];
        }
        if ($hasNull) {
            $annotations[] = 'null';
        }

        return new ResolvedAnnotation(implode('|', $annotations), $imports);
    }

    private function renderInt(Schema $schema): string
    {
        if ($schema->minimum === null && $schema->maximum === null) {
            return 'int';
        }

        $min = $schema->minimum !== null ? (string)$schema->minimum : 'min';
        $max = $schema->maximum !== null ? (string)$schema->maximum : 'max';

        return "int<{$min}, {$max}>";
    }

    private function renderList(Schema $schema): ResolvedAnnotation
    {
        $itemSchema = $schema->items ?? new Schema();
        $rendered = $this->renderRequired($itemSchema);
        $prefix = $schema->minItems !== null && $schema->minItems >= 1 ? 'non-empty-list' : 'list';

        return new ResolvedAnnotation("{$prefix}<{$rendered->annotation}>", $rendered->imports);
    }

    /**
     * @param list<string|int> $values
     */
    private function renderEnum(array $values): string
    {
        return implode('|', array_map(
            fn(string|int $v) => is_string($v) ? "'{$v}'" : (string)$v,
            $values,
        ));
    }

    private function isMixed(Schema $schema): bool
    {
        return $schema->type === null
            && $schema->anyOf === null
            && $schema->ref === null
            && $schema->enum === null;
    }
}
