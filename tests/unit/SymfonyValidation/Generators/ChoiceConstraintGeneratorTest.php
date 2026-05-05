<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\BoolType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ClassRefType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\NullType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UndefinedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\ChoiceConstraintGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Choice;

final class ChoiceConstraintGeneratorTest extends TestCase
{
    /**
     * @return iterable<string, array{PhpType, ?Constraint}>
     */
    public static function provideCases(): iterable
    {
        $choice = new Constraint(Choice::class, ['choices' => ['a', 'b']]);
        $enum   = new EnumLiteralType(['a', 'b']);

        yield 'enum literal'           => [$enum, $choice];
        yield 'nullable enum'          => [new UnionType([$enum, new NullType()]), $choice];
        yield 'optional enum'          => [new UnionType([$enum, new UndefinedType()]), $choice];
        yield 'optional nullable enum' => [new UnionType([$enum, new NullType(), new UndefinedType()]), $choice];

        yield 'string'                 => [new StringType(), null];
        yield 'int'                    => [new IntType(), null];
        yield 'bool'                   => [new BoolType(), null];
        yield 'union without enum'     => [new UnionType([new StringType(), new IntType()]), null];
        yield 'nullable string'        => [new UnionType([new StringType(), new NullType()]), null];
        yield 'enum and class ref'     => [
            new UnionType([$enum, new ClassRefType('Foo', 'App\\Dto\\Foo')]),
            null,
        ];
        yield 'enum class ref and null' => [
            new UnionType([$enum, new ClassRefType('Foo', 'App\\Dto\\Foo'), new NullType()]),
            null,
        ];
    }

    #[DataProvider('provideCases')]
    public function testGenerate(PhpType $type, ?Constraint $expected): void
    {
        self::assertEquals($expected, new ChoiceConstraintGenerator()->generate($type));
    }
}
