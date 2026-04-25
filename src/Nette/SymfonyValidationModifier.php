<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\ClassResolvedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\ScalarResolvedType;
use Nette\PhpGenerator\Literal;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class SymfonyValidationModifier implements PropertyModifier
{
    /**
     * @param PropertyGenerator<ScalarResolvedType|ClassResolvedType|null> $typeGenerator
     */
    public function __construct(
        private PropertyGenerator $typeGenerator,
    ) {
    }

    public function modify(object $context): void
    {
        $type = $this->typeGenerator->generate($context->propertySchema);
        if ($type === null) {
            return;
        }

        $context->namespace->addUse('Symfony\Component\Validator\Constraints', 'Assert');
        if ($type instanceof ClassResolvedType) {
            $context->namespace->addUse($type->className, $type->alias);
            $context->parameter->addAttribute(Type::class, [new Literal($type->alias . '::class')]);

            return;
        }

        $context->parameter->addAttribute(Type::class, [$type->type]);
    }
}
