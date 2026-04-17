<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\AnnotationResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;

final class ArrayAnnotation implements AnnotationResolver
{
    public function resolve(Schema $schema, PropertyGenerator $delegate): ?ResolvedAnnotation
    {
        if ($schema->type !== SchemaType::Array) {
            return null;
        }

        if ($schema->items !== null) {
            $itemResult = $delegate->generate($schema->items);
            $itemAnnotation = $itemResult->annotation;
            $imports = $itemResult->imports;
        } else {
            $itemAnnotation = 'mixed';
            $imports = [];
        }

        $nonEmpty = $schema->minItems !== null && $schema->minItems >= 1;
        $annotation = $nonEmpty
            ? 'non-empty-list<' . $itemAnnotation . '>'
            : 'list<' . $itemAnnotation . '>';

        return new ResolvedAnnotation($annotation, $imports);
    }
}
