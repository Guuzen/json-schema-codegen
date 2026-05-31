<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\NewObjectDefaultValue;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Nette\PhpGenerator\Literal;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class OptionalModifier implements PropertyModifier
{
    public function __construct(
        private DefaultGenerator $generator,
    ) {
    }

    public function modify(object $context): void
    {
        $default = $this->generator->generate($context->schema);

        if ($default !== null) {
            $value = $default->value instanceof NewObjectDefaultValue
                ? Literal::new($default->value->className, $default->value->args)
                : $default->value;
            $context->parameter->setDefaultValue($value);
        }
    }
}
