<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final class DefaultCommentGenerator implements CommentGenerator
{
    public function generate(Schema $schema): ?string
    {
        $anyOfDescriptions = [];
        foreach ($schema->anyOf ?? [] as $branch) {
            if ($branch->description !== null) {
                $anyOfDescriptions[] = $branch->description;
            }
        }

        $joinedAnyOfDescriptions = implode("\n", $anyOfDescriptions);

        if ($schema->description !== null && $anyOfDescriptions !== []) {
            return "$schema->description\n\n$joinedAnyOfDescriptions";
        }

        if ($anyOfDescriptions === []) {
            return $schema->description;
        }

        return $joinedAnyOfDescriptions;
    }
}
