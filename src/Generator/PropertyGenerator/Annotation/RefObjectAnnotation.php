<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\AnnotationResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;

final readonly class RefObjectAnnotation implements AnnotationResolver
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
        private SchemaRegistry $registry,
    ) {
    }

    public function resolve(Schema $schema, PropertyGenerator $delegate): ?ResolvedAnnotation
    {
        if ($schema->ref === null) {
            return null;
        }

        $referencedSchema = $this->registry->get($schema->ref->uri);

        if ($referencedSchema->type !== SchemaType::Object) {
            return null;
        }

        $fqcn = $this->fqcnResolver->fromUri($schema->ref->uri);
        $alias = $schema->title ?? $fqcn->className();

        return new ResolvedAnnotation($alias, [['alias' => $alias, 'fqcn' => $fqcn->fqcn]]);
    }
}
