<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\AnnotationResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;

final class UnionAnnotation implements AnnotationResolver
{
    public function resolve(Schema $schema, PropertyGenerator $delegate): ?ResolvedAnnotation
    {
        if ($schema->oneOf === null && !is_array($schema->type)) {
            return null;
        }

        if ($schema->oneOf !== null) {
            [$nonNullBranches, $hasNull] = $this->nonNullOneOfBranches($schema);
            $branchResults = array_map(fn(Schema $b) => $delegate->generate($b), $nonNullBranches);
            $parts = array_map(fn(ResolvedAnnotation $r) => $r->annotation, $branchResults);
            $imports = array_merge(...array_map(fn(ResolvedAnnotation $r) => $r->imports, $branchResults));
            if ($hasNull) {
                $parts[] = 'null';
            }

            return new ResolvedAnnotation(count($parts) === 0 ? 'mixed' : implode('|', $parts), $imports);
        }

        assert(is_array($schema->type));
        $hasNull = in_array(SchemaType::Null, $schema->type, true);
        $nonNullTypes = array_values(array_filter($schema->type, static fn(SchemaType $t) => $t !== SchemaType::Null));
        $branchResults = array_map(fn(SchemaType $t) => $delegate->generate(new Schema(type: $t)), $nonNullTypes);
        $parts = array_map(fn(ResolvedAnnotation $r) => $r->annotation, $branchResults);
        $imports = array_merge(...array_map(fn(ResolvedAnnotation $r) => $r->imports, $branchResults));
        if ($hasNull) {
            $parts[] = 'null';
        }

        return new ResolvedAnnotation(implode('|', $parts), $imports);
    }

    /**
     * @return array{list<Schema>, bool}
     */
    private function nonNullOneOfBranches(Schema $schema): array
    {
        $oneOf = $schema->oneOf ?? [];
        $nonNullBranches = array_values(array_filter($oneOf, static fn(Schema $b) => $b->type !== SchemaType::Null));
        $hasNull = count($nonNullBranches) < count($oneOf);

        return [$nonNullBranches, $hasNull];
    }
}
