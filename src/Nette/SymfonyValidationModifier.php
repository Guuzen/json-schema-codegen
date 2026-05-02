<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\TypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\TypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Nette\PhpGenerator\Literal;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class SymfonyValidationModifier implements PropertyModifier
{
    public function __construct(
        private TypeRenderer $typeRenderer,
        private TypeGenerator $typeGenerator,
    )
    {
    }

    public function modify(object $context): void
    {
        $type = $this->typeGenerator->generate($context->propertySchema);

        $resolvedTypes = $this->typeRenderer->render($type);
        if ($resolvedTypes === []) {
            return;
        }

        $context->namespace->addUse('Symfony\Component\Validator\Constraints', 'Assert');

        $withImports = [];
        $withoutImports = [];

        foreach ($resolvedTypes as $resolvedType) {
            if ($resolvedType->import === null) {
                $withoutImports[] = $resolvedType;
            } else {
                $withImports[] = $resolvedType;
            }
        }

        foreach ($withImports as $resolvedType) {
            $context->namespace->addUse(
                /** @phpstan-ignore offsetAccess.notFound */
                $resolvedType->import['fqcn'],
                /** @phpstan-ignore offsetAccess.notFound */
                $resolvedType->import['alias']
            );
        }

        $types = [];

        foreach ($withoutImports as $resolvedType) {
            $types[] = $resolvedType->type;
        }

        foreach ($withImports as $resolvedType) {
            $types[] = new Literal($resolvedType->type . '::class');
        }

        if (count($types) === 1) {
            $context->parameter->addAttribute(Type::class, [$types[0]]);

            return;
        }

        $context->parameter->addAttribute(Type::class, [$types]);
    }
}
