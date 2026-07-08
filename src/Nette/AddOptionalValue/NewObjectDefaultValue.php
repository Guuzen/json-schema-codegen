<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette\AddOptionalValue;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\AddDefaultValue;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Parameter;

/**
 * @implements AddDefaultValue<Parameter>
 */
final readonly class NewObjectDefaultValue implements AddDefaultValue
{
    /**
     * @param array<array-key, mixed> $args
     */
    public function __construct(
        public string $className,
        public array $args,
    ) {
    }

    public function addTo($context): void
    {
        $context->setDefaultValue(
            Literal::new($this->className, $this->args)
        );
    }
}
