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
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\ResolvedType;
use Guuzen\JsonSchemaCodegen\Undefined;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeGeneratorTest extends TestCase
{
    /**
     * @return iterable<string, array{PhpType, list<ResolvedType>}>
     */
    public static function provideTypes(): iterable
    {
        // Primitives
        yield 'string'    => [new StringType(), [new ResolvedType('string')]];
        yield 'integer'   => [new IntType(), [new ResolvedType('integer')]];
        yield 'float'     => [new FloatType(), [new ResolvedType('float')]];
        yield 'bool'      => [new BoolType(), [new ResolvedType('bool')]];
        yield 'null'      => [new NullType(), []];
        yield 'object'    => [new ObjectType(), [new ResolvedType('object')]];
        yield 'mixed'     => [new MixedType(), []];
        yield 'list'      => [new ListType(new MixedType()), [new ResolvedType('list')]];
        yield 'undefined' => [
            new UndefinedType(),
            [new ResolvedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class])],
        ];

        // Class refs
        yield 'class ref' => [
            new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'),
            [new ResolvedType('HomeAddress', ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address'])],
        ];

        // Enums
        yield 'string enum'        => [new EnumLiteralType(['a', 'b']), [new ResolvedType('string')]];
        yield 'integer enum'       => [new EnumLiteralType([1, 2]), [new ResolvedType('integer')]];
        yield 'mixed int+str enum' => [new EnumLiteralType(['a', 1]), [new ResolvedType('string'), new ResolvedType('integer')]];

        // Unions
        yield 'union string null' => [
            new UnionType([new StringType(), new NullType()]),
            [new ResolvedType('string')],
        ];
        yield 'union int string' => [
            new UnionType([new IntType(), new StringType()]),
            [new ResolvedType('integer'), new ResolvedType('string')],
        ];
        yield 'union ref null' => [
            new UnionType([new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'), new NullType()]),
            [new ResolvedType('HomeAddress', ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address'])],
        ];
        yield 'union two refs' => [
            new UnionType([
                new ClassRefType('CreditCardPayment', 'App\\Dto\\CreditCardPayment'),
                new ClassRefType('BankTransferPayment', 'App\\Dto\\BankTransferPayment'),
            ]),
            [
                new ResolvedType('CreditCardPayment', ['alias' => 'CreditCardPayment', 'fqcn' => 'App\\Dto\\CreditCardPayment']),
                new ResolvedType('BankTransferPayment', ['alias' => 'BankTransferPayment', 'fqcn' => 'App\\Dto\\BankTransferPayment']),
            ],
        ];
        yield 'union string undefined (optional)' => [
            new UnionType([new StringType(), new UndefinedType()]),
            [new ResolvedType('string'), new ResolvedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class])],
        ];
        yield 'union ref undefined (optional)' => [
            new UnionType([
                new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'),
                new UndefinedType(),
            ]),
            [
                new ResolvedType('HomeAddress', ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address']),
                new ResolvedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class]),
            ],
        ];
        yield 'union int string undefined (optional multi)' => [
            new UnionType([new IntType(), new StringType(), new UndefinedType()]),
            [
                new ResolvedType('integer'),
                new ResolvedType('string'),
                new ResolvedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class]),
            ],
        ];
    }

    /**
     * @param list<ResolvedType> $expected
     */
    #[DataProvider('provideTypes')]
    public function testType(PhpType $type, array $expected): void
    {
        self::assertEquals($expected, new DefaultTypeGenerator()->generate($type));
    }
}
