<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\DefaultTypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\BoolType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\ClassRefType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\FloatType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\MixedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\NullType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\ObjectType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\UndefinedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\ResolvedTypes;
use Guuzen\JsonSchemaCodegen\Undefined;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeGeneratorTest extends TestCase
{
    /**
     * @return iterable<string, array{PhpType, ResolvedTypes}>
     */
    public static function provideTypes(): iterable
    {
        // Primitives
        yield 'string'    => [new StringType(), new ResolvedTypes(['string'], [])];
        yield 'integer'   => [new IntType(), new ResolvedTypes(['integer'], [])];
        yield 'float'     => [new FloatType(), new ResolvedTypes(['float'], [])];
        yield 'bool'      => [new BoolType(), new ResolvedTypes(['bool'], [])];
        yield 'null'      => [new NullType(), new ResolvedTypes([], [])];
        yield 'object'    => [new ObjectType(), new ResolvedTypes(['object'], [])];
        yield 'mixed'     => [new MixedType(), new ResolvedTypes([], [])];
        yield 'list'      => [new ListType(new MixedType()), new ResolvedTypes(['list'], [])];
        yield 'undefined' => [
            new UndefinedType(),
            new ResolvedTypes(['Undefined'], [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
        ];

        // Class refs
        yield 'class ref' => [
            new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'),
            new ResolvedTypes(['HomeAddress'], [['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address']]),
        ];

        // Enums
        yield 'string enum'        => [new EnumLiteralType(['a', 'b']), new ResolvedTypes(['string'], [])];
        yield 'integer enum'       => [new EnumLiteralType([1, 2]), new ResolvedTypes(['integer'], [])];
        yield 'mixed int+str enum' => [new EnumLiteralType(['a', 1]), new ResolvedTypes(['string', 'integer'], [])];

        // Unions
        yield 'union string null' => [
            new UnionType([new StringType(), new NullType()]),
            new ResolvedTypes(['string'], []),
        ];
        yield 'union int string' => [
            new UnionType([new IntType(), new StringType()]),
            new ResolvedTypes(['integer', 'string'], []),
        ];
        yield 'union ref null' => [
            new UnionType([new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'), new NullType()]),
            new ResolvedTypes(['HomeAddress'], [['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address']]),
        ];
        yield 'union two refs' => [
            new UnionType([
                new ClassRefType('CreditCardPayment', 'App\\Dto\\CreditCardPayment'),
                new ClassRefType('BankTransferPayment', 'App\\Dto\\BankTransferPayment'),
            ]),
            new ResolvedTypes(
                ['CreditCardPayment', 'BankTransferPayment'],
                [
                    ['alias' => 'CreditCardPayment', 'fqcn' => 'App\\Dto\\CreditCardPayment'],
                    ['alias' => 'BankTransferPayment', 'fqcn' => 'App\\Dto\\BankTransferPayment'],
                ],
            ),
        ];
        yield 'union string undefined (optional)' => [
            new UnionType([new StringType(), new UndefinedType()]),
            new ResolvedTypes(['string', 'Undefined'], [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
        ];
        yield 'union ref undefined (optional)' => [
            new UnionType([
                new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'),
                new UndefinedType(),
            ]),
            new ResolvedTypes(
                ['HomeAddress', 'Undefined'],
                [
                    ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address'],
                    ['alias' => 'Undefined', 'fqcn' => Undefined::class],
                ],
            ),
        ];
        yield 'union int string undefined (optional multi)' => [
            new UnionType([new IntType(), new StringType(), new UndefinedType()]),
            new ResolvedTypes(
                ['integer', 'string', 'Undefined'],
                [['alias' => 'Undefined', 'fqcn' => Undefined::class]],
            ),
        ];
    }

    #[DataProvider('provideTypes')]
    public function testType(PhpType $type, ResolvedTypes $expected): void
    {
        self::assertEquals($expected, new DefaultTypeGenerator()->generate($type));
    }
}
