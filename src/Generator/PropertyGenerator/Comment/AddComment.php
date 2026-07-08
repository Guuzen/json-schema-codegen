<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment;

/**
 * @template Context
 */
interface AddComment
{
    /**
     * @param Context $context
     */
    public function addTo($context): void;
}
