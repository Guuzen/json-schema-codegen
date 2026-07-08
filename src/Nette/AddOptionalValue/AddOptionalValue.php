<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddOptionalValue;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\Nette\PropertyContext;
use Nette\PhpGenerator\Parameter;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class AddOptionalValue implements PropertyModifier
{
    /**
     * @param DefaultGenerator<Parameter> $generator
     */
    public function __construct(
        private DefaultGenerator $generator,
    ) {
    }

    public function modify(object $context): void
    {
        $default = $this->generator->generate($context->schema, $context->refNames);
        $default->addTo($context->parameter);
    }
}
