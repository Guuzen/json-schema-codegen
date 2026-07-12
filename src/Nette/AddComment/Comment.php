<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddComment;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\AddComment;
use Nette\PhpGenerator\Parameter;

/**
 * @implements AddComment<Parameter>
 */
final readonly class Comment implements AddComment
{
    public function __construct(public string $comment)
    {
    }

    public function addTo($context): void
    {
        $context->addComment($this->comment);
    }
}
