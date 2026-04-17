<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultValue;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\NewObjectDefaultValue;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Nette\PhpGenerator\Literal;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class DefaultModifier implements PropertyModifier
{
    /**
     * @param PropertyGenerator<?DefaultValue> $generator
     */
    public function __construct(
        private PropertyGenerator $generator,
    )
    {
    }

    public function modify(object $context): void
    {
        $default = $this->generator->generate($context->propertySchema);

        if ($default !== null) {
            $value = $default->value instanceof NewObjectDefaultValue
                ? Literal::new($default->value->className, $default->value->args)
                : $default->value;
            $context->parameter->setDefaultValue($value);
        }
    }
}
