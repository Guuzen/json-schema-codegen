<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\AnnotationResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;

final class StringAnnotation implements AnnotationResolver
{
    public function resolve(Schema $schema, PropertyGenerator $delegate): ?ResolvedAnnotation
    {
        if ($schema->type !== SchemaType::String) {
            return null;
        }

        return new ResolvedAnnotation(
            ($schema->minLength !== null && $schema->minLength >= 1) ? 'non-empty-string' : 'string',
            [],
        );
    }
}
