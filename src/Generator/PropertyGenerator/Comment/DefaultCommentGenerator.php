<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

/**
 * @template Context
 *
 * @implements CommentGenerator<Context>
 */
final readonly class DefaultCommentGenerator implements CommentGenerator
{
    /**
     * @param CommentFactory<Context> $commentFactory
     */
    public function __construct(
        private CommentFactory $commentFactory,
    ) {
    }

    public function generate(Schema $schema): AddComment
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
            return $this->commentFactory->comment("$description\n\n$joinedAnyOfDescriptions");
        }

        if ($anyOfDescriptions === []) {
            return $description === null
                ? $this->commentFactory->noComment()
                : $this->commentFactory->comment($description);
        }

        return $this->commentFactory->comment($joinedAnyOfDescriptions);
    }
}
