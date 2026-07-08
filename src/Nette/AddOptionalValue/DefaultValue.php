<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddOptionalValue;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\AddDefaultValue;
use Nette\PhpGenerator\Parameter;

/**
 * @implements AddDefaultValue<Parameter>
 */
final readonly class DefaultValue implements AddDefaultValue
{
    public function __construct(public mixed $value)
    {
    }

    public function addTo($context): void
    {
        $context->setDefaultValue($this->value);
    }
}
