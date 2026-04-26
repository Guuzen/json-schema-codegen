<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpTypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\ResolvedTypes;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\DefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Guuzen\JsonSchemaCodegen\Undefined;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeGeneratorTest extends TestCase
{
    private static function generate(Schema $schema, SchemaRegistry $registry = new SchemaRegistry([])): ResolvedTypes
    {
        $phpTypeGenerator = new PhpTypeGenerator(
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
            $registry,
        );

        return new TypeGenerator()->generate($schema, ['type' => $phpTypeGenerator->generate($schema)]);
    }

    public function testOptionalWithoutDefaultAddsUndefined(): void
    {
        self::assertEquals(
            new ResolvedTypes(['string', 'Undefined'], [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
            self::generate(new Schema(type: SchemaType::String, required: false)),
        );
    }

    public function testOptionalWithDefaultDoesNotAddUndefined(): void
    {
        self::assertEquals(
            new ResolvedTypes(['string'], []),
            self::generate(new Schema(type: SchemaType::String, required: false, default: new DefaultValue('foo'))),
        );
    }

    /**
     * @return iterable<string, array{Schema, ResolvedTypes}>
     */
    public static function provideScalarTypes(): iterable
    {
        yield 'string'  => [new Schema(type: SchemaType::String), new ResolvedTypes(['string'], [])];
        yield 'integer' => [new Schema(type: SchemaType::Integer), new ResolvedTypes(['integer'], [])];
        yield 'number'  => [new Schema(type: SchemaType::Number), new ResolvedTypes(['float'], [])];
        yield 'boolean' => [new Schema(type: SchemaType::Boolean), new ResolvedTypes(['bool'], [])];
        yield 'array'   => [new Schema(type: SchemaType::Array), new ResolvedTypes(['list'], [])];
        yield 'object'  => [new Schema(type: SchemaType::Object), new ResolvedTypes(['object'], [])];
        yield 'null'         => [new Schema(type: SchemaType::Null), new ResolvedTypes([], [])];
        yield 'unknown'      => [new Schema(), new ResolvedTypes([], [])];
        yield 'string enum'       => [new Schema(enum: ['a', 'b']), new ResolvedTypes(['string'], [])];
        yield 'integer enum'      => [new Schema(enum: [1, 2]), new ResolvedTypes(['integer'], [])];
        yield 'mixed int+str enum' => [new Schema(enum: ['a', 1]), new ResolvedTypes(['string', 'integer'], [])];
    }

    #[DataProvider('provideScalarTypes')]
    public function testScalarType(Schema $schema, ResolvedTypes $expected): void
    {
        self::assertEquals($expected, self::generate($schema));
    }

    /**
     * @return iterable<string, array{Schema, ResolvedTypes}>
     */
    public static function provideRefTypes(): iterable
    {
        yield 'ref to scalar' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Quantity.json'))),
            new ResolvedTypes(['integer'], []),
        ];
        yield 'ref to object' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/address/Address.json')), title: 'HomeAddress'),
            new ResolvedTypes(
                ['HomeAddress'],
                [['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address']],
            ),
        ];
        yield 'ref to string enum' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/OrderStatus.json'))),
            new ResolvedTypes(['string'], []),
        ];
        yield 'optional ref includes undefined' => [
            new Schema(
                required: false,
                ref: new Ref(new AbsoluteUri('file:///schemas/address/Address.json')),
                title: 'HomeAddress',
            ),
            new ResolvedTypes(
                ['HomeAddress', 'Undefined'],
                [
                    ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address'],
                    ['alias' => 'Undefined', 'fqcn' => Undefined::class],
                ],
            ),
        ];
    }

    #[DataProvider('provideRefTypes')]
    public function testRefType(Schema $schema, ResolvedTypes $expected): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Quantity.json'         => new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100),
            'file:///schemas/address/Address.json'  => new Schema(type: SchemaType::Object),
            'file:///schemas/OrderStatus.json'      => new Schema(enum: ['pending', 'processing', 'shipped']),
        ]);

        self::assertEquals($expected, self::generate($schema, $registry));
    }

    /**
     * @return iterable<string, array{Schema, ResolvedTypes}>
     */
    public static function provideUnionTypes(): iterable
    {
        yield 'nullable string from type array' => [
            new Schema(type: [SchemaType::String, SchemaType::Null]),
            new ResolvedTypes(['string'], []),
        ];
        yield 'nullable string from oneOf' => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Null)]),
            new ResolvedTypes(['string'], []),
        ];
        yield 'multi type union' => [
            new Schema(type: [SchemaType::Integer, SchemaType::String]),
            new ResolvedTypes(['integer', 'string'], []),
        ];
        yield 'multi branch oneOf' => [
            new Schema(oneOf: [new Schema(type: SchemaType::Integer), new Schema(type: SchemaType::String)]),
            new ResolvedTypes(['integer', 'string'], []),
        ];
        yield 'optional multi type union includes undefined' => [
            new Schema(type: [SchemaType::Integer, SchemaType::String], required: false),
            new ResolvedTypes(
                ['integer', 'string', 'Undefined'],
                [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
        ];
        yield 'nullable ref from oneOf' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Quantity.json'))),
                new Schema(type: SchemaType::Null),
            ]),
            new ResolvedTypes(['integer'], []),
        ];
        yield 'nullable ref to object from oneOf' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/address/Address.json')), title: 'HomeAddress'),
                new Schema(type: SchemaType::Null),
            ]),
            new ResolvedTypes(
                ['HomeAddress'],
                [['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\address\\Address']],
            ),
        ];
        yield 'multi ref oneOf' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/CreditCardPayment.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/BankTransferPayment.json'))),
            ]),
            new ResolvedTypes(
                ['CreditCardPayment', 'BankTransferPayment'],
                [
                    ['alias' => 'CreditCardPayment', 'fqcn' => 'App\\Dto\\CreditCardPayment'],
                    ['alias' => 'BankTransferPayment', 'fqcn' => 'App\\Dto\\BankTransferPayment'],
                ],
            ),
        ];
    }

    #[DataProvider('provideUnionTypes')]
    public function testUnionType(Schema $schema, ResolvedTypes $expected): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Quantity.json'             => new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100),
            'file:///schemas/address/Address.json'      => new Schema(type: SchemaType::Object),
            'file:///schemas/CreditCardPayment.json'    => new Schema(type: SchemaType::Object),
            'file:///schemas/BankTransferPayment.json'  => new Schema(type: SchemaType::Object),
        ]);

        self::assertEquals($expected, self::generate($schema, $registry));
    }
}
