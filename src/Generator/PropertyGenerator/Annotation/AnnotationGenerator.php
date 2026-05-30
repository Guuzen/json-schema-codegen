<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\SchemaTree;

interface AnnotationGenerator
{
    public function generate(SchemaTree $tree): ResolvedAnnotation;
}
