<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final class DefaultCommentGenerator implements CommentGenerator
{
    public function generate(Schema $schema): ?string
    {
        $description = $schema->description;

        $anyOfDescriptions = [];
        foreach ($schema->anyOf ?? [] as $branch) {
            if ($branch->description !== null) {
                $anyOfDescriptions[] = $branch->description;
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
