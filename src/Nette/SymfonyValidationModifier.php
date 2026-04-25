<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\ResolvedTypes;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Nette\PhpGenerator\Literal;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class SymfonyValidationModifier implements PropertyModifier
{
    /**
     * @param PropertyGenerator<ResolvedTypes> $typeGenerator
     */
    public function __construct(
        private PropertyGenerator $typeGenerator,
    ) {
    }

    public function modify(object $context): void
    {
        $resolvedTypes = $this->typeGenerator->generate($context->propertySchema);
        if ($resolvedTypes->types === []) {
            return;
        }

        $context->namespace->addUse('Symfony\Component\Validator\Constraints', 'Assert');
        foreach ($resolvedTypes->imports as $import) {
            $context->namespace->addUse($import['fqcn'], $import['alias']);
        }

        $importAliases = array_column($resolvedTypes->imports, 'alias');
        $types = array_map(
            static fn(string $type): string|Literal => in_array($type, $importAliases, true)
                ? new Literal($type . '::class')
                : $type,
            $resolvedTypes->types,
        );

        if (count($types) === 1) {
            $context->parameter->addAttribute(Type::class, [$types[0]]);

            return;
        }

        $context->parameter->addAttribute(Type::class, [$types]);
    }
}
