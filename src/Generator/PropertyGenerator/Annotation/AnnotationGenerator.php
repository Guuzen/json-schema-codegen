<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

interface AnnotationGenerator
{
    public function generate(Schema $schema, RefNames $refNames): string;
}
