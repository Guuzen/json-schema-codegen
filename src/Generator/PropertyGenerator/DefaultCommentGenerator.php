<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final class DefaultCommentGenerator implements CommentGenerator
{
    public function generate(Schema $schema): ?string
    {
        return $schema->description;
    }
}
