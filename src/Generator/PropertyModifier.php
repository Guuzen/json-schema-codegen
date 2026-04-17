<?php
declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

/**
 * @template TContext of object
 */
interface PropertyModifier
{
    /**
     * @param TContext $context
     */
    public function modify(object $context): void;
}
