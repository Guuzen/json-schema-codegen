<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

/**
 * @implements PropertyGenerator<?string, null>
 */
final class CommentGenerator implements PropertyGenerator
{
    public function generate(Schema $schema, mixed $params): ?string
    {
        return $schema->description;
    }
}
