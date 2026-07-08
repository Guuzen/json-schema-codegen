<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddComment;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\AddComment;
use Nette\PhpGenerator\Parameter;

/**
 * @implements AddComment<Parameter>
 */
final class NoComment implements AddComment
{
    public function addTo($context): void
    {
    }
}
