<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\AnnotationResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class InlineRefAnnotation implements AnnotationResolver
{
    public function __construct(
        private SchemaRegistry $registry,
    ) {
    }

    public function resolve(Schema $schema, PropertyGenerator $delegate): ?ResolvedAnnotation
    {
        if ($schema->ref === null) {
            return null;
        }

        $referencedSchema = $this->registry->get($schema->ref->uri);

        return $delegate->generate($referencedSchema);
    }
}
