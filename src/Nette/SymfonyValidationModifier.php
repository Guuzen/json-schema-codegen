<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\TypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\DefaultTypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ClassConstantRef;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ClassRef;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintList;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\ConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\AllConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\ChoiceConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\DateConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\DateTimeConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\GreaterThanOrEqualConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\LessThanOrEqualConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\NotNullConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\RegexConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\TypeConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\UuidConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\ValidConstraintGenerator;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class SymfonyValidationModifier implements PropertyModifier
{
    /**
     * @param list<ConstraintGenerator> $generators
     */
    public function __construct(
        private TypeGenerator $typeGenerator,
        private array $generators,
    )
    {
    }

    public function modify(object $context): void
    {
        $type = $this->typeGenerator->generate($context->propertySchema);

        $context->namespace->addUse('Symfony\Component\Validator\Constraints', 'Assert');

        foreach ($this->generators as $generator) {
            $constraint = $generator->generate($type);
            if ($constraint !== null) {
                $context->parameter->addAttribute(
                    $constraint->name,
                    $this->convertArgs($constraint->args, $context->namespace),
                );
            }
        }
    }

    /**
     * @param array<int|string, mixed> $args
     * @return array<int|string, mixed>
     */
    private function convertArgs(array $args, PhpNamespace $namespace): array
    {
        $converted = [];
        foreach ($args as $key => $value) {
            $converted[$key] = $this->convertValue($value, $namespace);
        }
        return $converted;
    }

    private function convertValue(mixed $value, PhpNamespace $namespace): mixed
    {
        if ($value instanceof ClassRef) {
            $namespace->addUse($value->fqcn, $value->alias);
            return new Literal($value->alias . '::class');
        }

        if ($value instanceof ClassConstantRef) {
            $namespace->addUse($value->fqcn, $value->alias);
            return new Literal($value->alias . '::' . $value->constant);
        }

        if ($value instanceof ConstraintList) {
            return $this->renderConstraintList($value, $namespace);
        }

        if (is_array($value)) {
            return $this->convertArgs($value, $namespace);
        }

        return $value;
    }

    private function renderConstraintList(ConstraintList $list, PhpNamespace $namespace): Literal
    {
        $lines = ['['];
        foreach ($list->constraints as $constraint) {
            $lines[] = '    ' . Literal::new(
                $namespace->simplifyType($constraint->name),
                $this->convertArgs($constraint->args, $namespace),
            ) . ',';
        }
        $lines[] = ']';

        return new Literal(implode("\n", $lines));
    }

    /**
     * @return list<ConstraintGenerator>
     */
    public static function defaultGenerators(): array
    {
        return [
            new TypeConstraintGenerator(new DefaultTypeRenderer()),
            new NotNullConstraintGenerator(),
            new UuidConstraintGenerator(),
            new DateConstraintGenerator(),
            new DateTimeConstraintGenerator(),
            new RegexConstraintGenerator(),
            new GreaterThanOrEqualConstraintGenerator(),
            new LessThanOrEqualConstraintGenerator(),
            new ChoiceConstraintGenerator(),
            new AllConstraintGenerator(AllConstraintGenerator::defaultGenerators()),
            new ValidConstraintGenerator(),
        ];
    }
}
