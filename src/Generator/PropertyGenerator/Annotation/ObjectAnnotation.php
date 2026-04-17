<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\AnnotationResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class ObjectAnnotation implements AnnotationResolver
{
    public function resolve(Schema $schema, PropertyGenerator $delegate): ?ResolvedAnnotation
    {
        if ($schema->type !== SchemaType::Object) {
            return null;
        }

        return new ResolvedAnnotation('object', []);
    }
}
