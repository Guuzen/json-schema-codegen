<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

final class DefaultCommentGenerator implements CommentGenerator
{
    public function generate(Schema $schema): ?string
    {
        $oneOfDescriptions = [];
        foreach ($schema->oneOf ?? [] as $branch) {
            if ($branch->description !== null) {
                $oneOfDescriptions[] = $branch->description;
            }
        }

        $joinedOneOfDescriptions = implode("\n", $oneOfDescriptions);

        if ($schema->description !== null && $oneOfDescriptions !== []) {
            return "$schema->description\n\n$joinedOneOfDescriptions";
        }

        if ($oneOfDescriptions === []) {
            return $schema->description;
        }

        return $joinedOneOfDescriptions;
    }
}
