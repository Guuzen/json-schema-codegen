<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\CommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class CommentModifier implements PropertyModifier
{
    public function __construct(
        private CommentGenerator $generator,
    )
    {
    }

    public function modify(object $context): void
    {
        $comment = $this->generator->generate($context->propertySchema);

        if ($comment !== null) {
            $context->parameter->addComment($comment);
            $context->parameter->addComment('');
        }
    }
}
