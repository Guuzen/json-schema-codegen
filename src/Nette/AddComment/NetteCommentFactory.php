<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddComment;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\AddComment;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\CommentFactory;
use Nette\PhpGenerator\Parameter;

/**
 * @implements CommentFactory<Parameter>
 */
final class NetteCommentFactory implements CommentFactory
{
    public function comment(string $comment): AddComment
    {
        return new Comment($comment);
    }

    public function noComment(): AddComment
    {
        return new NoComment();
    }
}
