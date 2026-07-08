<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddOptionalValue;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\AddDefaultValue;
use Nette\PhpGenerator\Parameter;

/**
 * @implements AddDefaultValue<Parameter>
 */
final readonly class NoDefaultValue implements AddDefaultValue
{
    public function addTo($context): void
    {
    }
}
