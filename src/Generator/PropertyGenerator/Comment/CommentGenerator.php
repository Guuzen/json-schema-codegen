<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

use Guuzen\JsonSchemaCodegen\Generator\SchemaTree;

interface CommentGenerator
{
    public function generate(SchemaTree $tree): ?string;
}
