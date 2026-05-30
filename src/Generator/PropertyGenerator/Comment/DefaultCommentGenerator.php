<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

use Guuzen\JsonSchemaCodegen\Generator\SchemaTree;

final class DefaultCommentGenerator implements CommentGenerator
{
    public function generate(SchemaTree $tree): ?string
    {
        $description = $tree->schema->description;

        $anyOfDescriptions = [];
        foreach ($tree->anyOf ?? [] as $branch) {
            if ($branch->schema->description !== null) {
                $anyOfDescriptions[] = $branch->schema->description;
            }
        }

        $joinedAnyOfDescriptions = implode("\n", $anyOfDescriptions);

        if ($description !== null && $anyOfDescriptions !== []) {
            return "$description\n\n$joinedAnyOfDescriptions";
        }

        if ($anyOfDescriptions === []) {
            return $description;
        }

        return $joinedAnyOfDescriptions;
    }
}
