<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

/**
 * @implements PropertyGenerator<?string>
 */
final class CommentGenerator implements PropertyGenerator
{
    public function generate(Schema $schema): ?string
    {
        return $schema->description;
    }
}
