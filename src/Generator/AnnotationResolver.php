<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\ResolvedAnnotation;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

interface AnnotationResolver
{
    /**
     * @param PropertyGenerator<ResolvedAnnotation> $delegate
     */
    public function resolve(Schema $schema, PropertyGenerator $delegate): ?ResolvedAnnotation;
}
