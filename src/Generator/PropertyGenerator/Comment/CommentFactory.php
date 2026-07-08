<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

/**
 * @template Context
 */
interface CommentFactory
{
    /**
     * @return AddComment<Context>
     */
    public function comment(string $comment): AddComment;

    /**
     * @return AddComment<Context>
     */
    public function noComment(): AddComment;
}
