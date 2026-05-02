<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\DefaultTypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\BoolType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ClassRefType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\FloatType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\MixedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\NullType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ObjectType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UndefinedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\RenderedType;
use Guuzen\JsonSchemaCodegen\Undefined;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeRendererTest extends TestCase
{
    /**
     * @return iterable<string, array{PhpType, list<RenderedType>}>
     */
    public static function provideTypes(): iterable
    {
        // Primitives
        yield 'string'    => [new StringType(), [new RenderedType('string')]];
        yield 'integer'   => [new IntType(), [new RenderedType('integer')]];
        yield 'float'     => [new FloatType(), [new RenderedType('float')]];
        yield 'bool'      => [new BoolType(), [new RenderedType('bool')]];
        yield 'null'      => [new NullType(), []];
        yield 'object'    => [new ObjectType(), [new RenderedType('object')]];
        yield 'mixed'     => [new MixedType(), []];
        yield 'list'      => [new ListType(new MixedType()), [new RenderedType('list')]];
        yield 'undefined' => [
            new UndefinedType(),
            [new RenderedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class])],
        ];

        // Class refs
        yield 'class ref' => [
            new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'),
            [new RenderedType('HomeAddress', ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address'])],
        ];

        // Enums
        yield 'string enum'        => [new EnumLiteralType(['a', 'b']), [new RenderedType('string')]];
        yield 'integer enum'       => [new EnumLiteralType([1, 2]), [new RenderedType('integer')]];
        yield 'mixed int+str enum' => [new EnumLiteralType(['a', 1]), [new RenderedType('string'), new RenderedType('integer')]];

        // Unions
        yield 'union string null' => [
            new UnionType([new StringType(), new NullType()]),
            [new RenderedType('string')],
        ];
        yield 'union int string' => [
            new UnionType([new IntType(), new StringType()]),
            [new RenderedType('integer'), new RenderedType('string')],
        ];
        yield 'union ref null' => [
            new UnionType([new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'), new NullType()]),
            [new RenderedType('HomeAddress', ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address'])],
        ];
        yield 'union two refs' => [
            new UnionType([
                new ClassRefType('CreditCardPayment', 'App\\Dto\\CreditCardPayment'),
                new ClassRefType('BankTransferPayment', 'App\\Dto\\BankTransferPayment'),
            ]),
            [
                new RenderedType('CreditCardPayment', ['alias' => 'CreditCardPayment', 'fqcn' => 'App\\Dto\\CreditCardPayment']),
                new RenderedType('BankTransferPayment', ['alias' => 'BankTransferPayment', 'fqcn' => 'App\\Dto\\BankTransferPayment']),
            ],
        ];
        yield 'union string undefined (optional)' => [
            new UnionType([new StringType(), new UndefinedType()]),
            [new RenderedType('string'), new RenderedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class])],
        ];
        yield 'union ref undefined (optional)' => [
            new UnionType([
                new ClassRefType('HomeAddress', 'App\\Dto\\address\\Address'),
                new UndefinedType(),
            ]),
            [
                new RenderedType('HomeAddress', ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address']),
                new RenderedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class]),
            ],
        ];
        yield 'union int string undefined (optional multi)' => [
            new UnionType([new IntType(), new StringType(), new UndefinedType()]),
            [
                new RenderedType('integer'),
                new RenderedType('string'),
                new RenderedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class]),
            ],
        ];
    }

    /**
     * @param list<RenderedType> $expected
     */
    #[DataProvider('provideTypes')]
    public function testType(PhpType $type, array $expected): void
    {
        self::assertEquals($expected, new DefaultTypeRenderer()->render($type));
    }
}
