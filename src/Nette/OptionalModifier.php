<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\NewObjectDefaultValue;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\Literal;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class OptionalModifier implements PropertyModifier
{
    public function __construct(
        private DefaultGenerator $generator,
        private FqcnResolver $fqcnResolver,
        private AbsoluteUri $undefinedUri,
    ) {
    }

    public function modify(object $context): void
    {
        if ($context->tree->schema->required) {
            return;
        }

        $default = $this->generator->generate($context->tree);

        if ($default !== null) {
            $value = $default->value instanceof NewObjectDefaultValue
                ? Literal::new($default->value->className, $default->value->args)
                : $default->value;
            $context->parameter->setDefaultValue($value);

            return;
        }

        if ($context->tree->anyOf !== null) {
            foreach ($context->tree->anyOf as $branch) {
                if ($branch->schema->ref === null) {
                    continue;
                }
                if ($branch->schema->ref->uri->value === $this->undefinedUri->value) {
                    $fqcn = $this->fqcnResolver->fromUri($this->undefinedUri);
                    $context->namespace->addUse($fqcn->fqcn);
                    $context->parameter->setDefaultValue(Literal::new($fqcn->className()));
                }
            }
        }
    }
}
