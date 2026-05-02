<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\TypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\TypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Nette\PhpGenerator\Literal;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Constraints\Valid;

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
        } else {
            $context->parameter->addAttribute(Type::class, [$types]);
        }

        if (!$type->isNullable()) {
            $context->parameter->addAttribute(NotNull::class);
        }

        if ($type instanceof EnumLiteralType) {
            $context->parameter->addAttribute(Choice::class, ['choices' => $type->values]);
        }

        if ($type instanceof IntType) {
            if ($type->min !== null) {
                $context->parameter->addAttribute(GreaterThanOrEqual::class, [$type->min]);
            }
            if ($type->max !== null) {
                $context->parameter->addAttribute(LessThanOrEqual::class, [$type->max]);
            }
        }

        if ($type instanceof StringType && $type->format === 'uuid') {
            $context->parameter->addAttribute(Uuid::class);
        }

        if ($type instanceof ListType) {
            $itemConstraints = $this->collectItemConstraints($type->itemType);
            if ($itemConstraints !== []) {
                $constraintCode = $this->formatConstraintsArray($itemConstraints);
                $context->parameter->addAttribute(All::class, [new Literal($constraintCode)]);
            }
            if ($type->itemType->containsClassRef()) {
                $context->parameter->addAttribute(Valid::class);
            }
        } elseif ($type->containsClassRef()) {
            $context->parameter->addAttribute(Valid::class);
        }
    }

    /**
     * @param array<Literal> $constraints
     */
    private function formatConstraintsArray(array $constraints): string
    {
        $lines = ['['];
        foreach ($constraints as $constraint) {
            $lines[] = '    ' . $constraint . ',';
        }
        $lines[] = ']';
        return implode("\n", $lines);
    }

    /**
     * @return array<Literal>
     */
    private function collectItemConstraints(PhpType $itemType): array
    {
        $constraints = [];

        $resolvedTypes = $this->typeRenderer->render($itemType);
        if ($resolvedTypes !== []) {
            $withImports = [];
            $withoutImports = [];

            foreach ($resolvedTypes as $resolvedType) {
                if ($resolvedType->import === null) {
                    $withoutImports[] = $resolvedType;
                } else {
                    $withImports[] = $resolvedType;
                }
            }

            $types = [];

            foreach ($withoutImports as $resolvedType) {
                $types[] = "'" . $resolvedType->type . "'";
            }

            foreach ($withImports as $resolvedType) {
                $types[] = $resolvedType->type . '::class';
            }

            if (count($types) === 1) {
                $constraints[] = new Literal('new Assert\\Type(' . $types[0] . ')');
            } else {
                $constraints[] = new Literal('new Assert\\Type([' . implode(', ', $types) . '])');
            }
        }

        if (!$itemType->isNullable()) {
            $constraints[] = new Literal('new Assert\\NotNull()');
        }

        if ($itemType instanceof StringType && $itemType->format === 'uuid') {
            $constraints[] = new Literal('new Assert\\Uuid()');
        }

        if ($itemType instanceof IntType) {
            if ($itemType->min !== null) {
                $constraints[] = new Literal('new Assert\\GreaterThanOrEqual(' . $itemType->min . ')');
            }
            if ($itemType->max !== null) {
                $constraints[] = new Literal('new Assert\\LessThanOrEqual(' . $itemType->max . ')');
            }
        }

        if ($itemType instanceof EnumLiteralType) {
            $quoted = array_map(fn($v) => "'" . $v . "'", $itemType->values);
            $constraints[] = new Literal('new Assert\\Choice(choices: [' . implode(', ', $quoted) . '])');
        }

        return $constraints;
    }
}
