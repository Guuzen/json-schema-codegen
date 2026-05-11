<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\DefaultTypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
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
use Guuzen\JsonSchemaCodegen\SymfonyValidation\SchemaWalker;
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
        private array $generators,
    )
    {
    }

    public function modify(object $context): void
    {
        $context->namespace->addUse('Symfony\Component\Validator\Constraints', 'Assert');

        foreach ($this->generators as $generator) {
            $constraints = $generator->generate($context->propertySchema);
            foreach ($constraints as $constraint) {
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
    public static function defaultGenerators(
        FqcnResolver   $fqcnResolver,
        SchemaRegistry $registry,
    ): array {
        $walker = new SchemaWalker($registry);

        return [
            new TypeConstraintGenerator(new DefaultTypeRenderer($fqcnResolver, $registry)),
            new NotNullConstraintGenerator($registry),
            new UuidConstraintGenerator($walker),
            new DateConstraintGenerator($walker),
            new DateTimeConstraintGenerator($walker),
            new RegexConstraintGenerator($walker),
            new GreaterThanOrEqualConstraintGenerator($walker),
            new LessThanOrEqualConstraintGenerator($walker),
            new ChoiceConstraintGenerator($walker),
            new AllConstraintGenerator(
                AllConstraintGenerator::defaultGenerators($fqcnResolver, $registry, $walker),
                $walker,
            ),
            new ValidConstraintGenerator($walker, $registry),
        ];
    }
}
