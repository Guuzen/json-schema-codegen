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
use Nette\PhpGenerator\Attribute;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
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

        $context->namespace->addUse('Symfony\Component\Validator\Constraints', 'Assert');

        $attributes = $this->collectItemConstraints($type, $context->namespace);
        $attributes[] = $this->typeValid($type);

        foreach (array_filter($attributes) as $attribute) {
            $context->parameter->addAttribute($attribute->getName(), $attribute->getArguments());
        }
    }

    private function typeAll(PhpType $type, PhpNamespace $namespace): ?Attribute
    {
        if (!$type instanceof ListType) {
            return null;
        }

        $itemConstraints = array_values(
            array_filter($this->collectItemConstraints($type->itemType, $namespace))
        );

        if ($itemConstraints === []) {
            return null;
        }

        $lines = ['['];

        foreach ($itemConstraints as $constraint) {
            $class = $namespace->simplifyType($constraint->getName());
            $lines[] = '    ' . Literal::new($class, $constraint->getArguments()) . ',';
        }

        $lines[] = ']';

        $constraintCode = implode("\n", $lines);

        return new Attribute(All::class, [new Literal($constraintCode)]);
    }

    private function typeValid(PhpType $type): ?Attribute
    {
        if (!$type->containsClassRef()) {
            return null;
        }

        return new Attribute(Valid::class, []);
    }

    /**
     * @return non-empty-list<?Attribute>
     */
    private function collectItemConstraints(PhpType $type, PhpNamespace $namespace): array
    {
        $constraints = [];
        $constraints[] = $this->typeAttribute($type, $namespace);
        $constraints[] = $this->typeNotNull($type);
        $constraints[] = $this->typeUuid($type);
        $constraints[] = $this->typeGte($type);
        $constraints[] = $this->typeLte($type);
        $constraints[] = $this->typeEnum($type);
        $constraints[] = $this->typeAll($type, $namespace);

        return $constraints;
    }

    private function typeAttribute(PhpType $type, PhpNamespace $namespace): ?Attribute
    {
        $renderedTypes = $this->typeRenderer->render($type);

        $withImports = [];
        $withoutImports = [];

        foreach ($renderedTypes as $resolvedType) {
            if ($resolvedType->import === null) {
                $withoutImports[] = $resolvedType;
            } else {
                $withImports[] = $resolvedType;
            }
        }

        foreach ($withImports as $resolvedType) {
            $namespace->addUse(
                /** @phpstan-ignore offsetAccess.notFound */
                $resolvedType->import['fqcn'],
                /** @phpstan-ignore offsetAccess.notFound */
                $resolvedType->import['alias']
            );
        }

        $types = [];

        foreach ($withoutImports as $resolvedType) {
            $types[] = new Literal("'" . $resolvedType->type . "'");
        }

        foreach ($withImports as $resolvedType) {
            $types[] = new Literal($resolvedType->type . '::class');
        }

        $typesNumber = count($types);

        if ($typesNumber === 1) {
            return new Attribute(Type::class, [$types[0]]);
        }

        if ($typesNumber > 1) {
            return new Attribute(Type::class, [$types]);
        }

        return null;
    }

    private function typeNotNull(PhpType $type): ?Attribute
    {
        if ($type->isNullable()) {
            return null;
        }

        return new Attribute(NotNull::class, []);
    }

    private function typeEnum(PhpType $type): ?Attribute
    {
        if (!$type instanceof EnumLiteralType) {
            return null;
        }

        return new Attribute(Choice::class, ['choices' => $type->values]);
    }

    private function typeGte(PhpType $type): ?Attribute
    {
        if (!$type instanceof IntType || $type->min === null) {
            return null;
        }

        return new Attribute(GreaterThanOrEqual::class, [$type->min]);
    }

    private function typeLte(PhpType $type): ?Attribute
    {
        if (!$type instanceof IntType || $type->max === null) {
            return null;
        }

        return new Attribute(LessThanOrEqual::class, [$type->max]);
    }

    private function typeUuid(PhpType $type): ?Attribute
    {
        if (!$type instanceof StringType || $type->format !== 'uuid') {
            return null;
        }

        return new Attribute(Uuid::class, []);
    }
}
