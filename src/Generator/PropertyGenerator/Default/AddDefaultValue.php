<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

/**
 * @template Context
 */
interface AddDefaultValue
{
    /**
     * @param Context $context
     */
    public function addTo($context): void;
}
