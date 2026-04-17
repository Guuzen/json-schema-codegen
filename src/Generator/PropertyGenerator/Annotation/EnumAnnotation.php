<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\AnnotationResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final class EnumAnnotation implements AnnotationResolver
{
    public function resolve(Schema $schema, PropertyGenerator $delegate): ?ResolvedAnnotation
    {
        if ($schema->enum === null) {
            return null;
        }

        $annotation = implode('|', array_map(
            static fn(string|int $v) => is_string($v) ? "'" . $v . "'" : (string) $v,
            $schema->enum,
        ));

        return new ResolvedAnnotation($annotation, []);
    }
}
