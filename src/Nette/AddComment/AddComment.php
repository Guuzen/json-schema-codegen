<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddComment;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\CommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\Nette\PropertyContext;
use Nette\PhpGenerator\Parameter;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class AddComment implements PropertyModifier
{
    /**
     * @param CommentGenerator<Parameter> $generator
     */
    public function __construct(
        private CommentGenerator $generator,
    ) {
    }

    public function modify(object $context): void
    {
        $comment = $this->generator->generate($context->schema);
        $comment->addTo($context->parameter);
    }
}
