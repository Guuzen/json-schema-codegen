<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

interface CommentGenerator
{
    public function generate(Schema $schema): ?string;
}
