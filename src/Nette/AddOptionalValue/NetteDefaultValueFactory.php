<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddOptionalValue;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\AddDefaultValue;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultValueFactory;
use Nette\PhpGenerator\Parameter;

/**
 * @implements DefaultValueFactory<Parameter>
 */
final readonly class NetteDefaultValueFactory implements DefaultValueFactory
{
    public function defaultValue(mixed $value): AddDefaultValue
    {
        return new DefaultValue($value);
    }

    public function newObjectDefaultValue(string $className, array $args): AddDefaultValue
    {
        return new NewObjectDefaultValue($className, $args);
    }

    public function noDefaultValue(): AddDefaultValue
    {
        return new NoDefaultValue();
    }
}
