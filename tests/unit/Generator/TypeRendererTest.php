<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\DefaultTypeRenderer;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer\RenderedType;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\DefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Undefined;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeRendererTest extends TestCase
{
    private static function makeRenderer(SchemaRegistry $registry = new SchemaRegistry([])): DefaultTypeRenderer
    {
        return new DefaultTypeRenderer(
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
            $registry,
        );
    }

    /**
     * @return iterable<string, array{Schema, list<RenderedType>, SchemaRegistry}>
     */
    public static function provideCases(): iterable
    {
        $emptyRegistry = new SchemaRegistry([]);
        $undefined     = new RenderedType(
            'Undefined',
            ['alias' => 'Undefined', 'fqcn' => Undefined::class],
        );

        // Primitives
        yield 'string'  => [new Schema(type: SchemaType::String), [new RenderedType('string')], $emptyRegistry];
        yield 'integer' => [new Schema(type: SchemaType::Integer), [new RenderedType('integer')], $emptyRegistry];
        yield 'float'   => [new Schema(type: SchemaType::Number), [new RenderedType('float')], $emptyRegistry];
        yield 'bool'    => [new Schema(type: SchemaType::Boolean), [new RenderedType('bool')], $emptyRegistry];
        yield 'null'    => [new Schema(type: SchemaType::Null), [], $emptyRegistry];
        yield 'object'  => [new Schema(type: SchemaType::Object), [new RenderedType('object')], $emptyRegistry];
        yield 'mixed'   => [new Schema(), [], $emptyRegistry];
        yield 'list'    => [new Schema(type: SchemaType::Array), [new RenderedType('list')], $emptyRegistry];

        // Enums
        yield 'string enum' => [
            new Schema(enum: ['a', 'b']),
            [new RenderedType('string')],
            $emptyRegistry,
        ];
        yield 'integer enum' => [
            new Schema(enum: [1, 2]),
            [new RenderedType('integer')],
            $emptyRegistry,
        ];
        yield 'mixed int+str enum' => [
            new Schema(enum: ['a', 1]),
            [new RenderedType('string'), new RenderedType('integer')],
            $emptyRegistry,
        ];

        // Unions
        yield 'anyOf string and null' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String),
                new Schema(type: SchemaType::Null),
            ]),
            [new RenderedType('string')],
            $emptyRegistry,
        ];
        yield 'anyOf int and string' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::Integer),
                new Schema(type: SchemaType::String),
            ]),
            [new RenderedType('integer'), new RenderedType('string')],
            $emptyRegistry,
        ];
        yield 'type array string and null' => [
            new Schema(type: [SchemaType::String, SchemaType::Null]),
            [new RenderedType('string')],
            $emptyRegistry,
        ];

        // Class refs
        $addressRegistry = new SchemaRegistry([
            'file:///schemas/Address.json' => new Schema(type: SchemaType::Object),
        ]);
        yield 'class ref via $ref' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Address.json'))),
            [new RenderedType('Address', ['alias' => 'Address', 'fqcn' => 'App\\Dto\\Address'])],
            $addressRegistry,
        ];
        yield 'class ref with alias' => [
            new Schema(
                ref: new Ref(new AbsoluteUri('file:///schemas/Address.json')),
                xAlias: 'HomeAddress',
            ),
            [new RenderedType('HomeAddress', ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\Address'])],
            $addressRegistry,
        ];
        yield 'anyOf two refs' => [
            new Schema(anyOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/CreditCardPayment.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/BankTransferPayment.json'))),
            ]),
            [
                new RenderedType('CreditCardPayment', [
                    'alias' => 'CreditCardPayment',
                    'fqcn'  => 'App\\Dto\\CreditCardPayment',
                ]),
                new RenderedType('BankTransferPayment', [
                    'alias' => 'BankTransferPayment',
                    'fqcn'  => 'App\\Dto\\BankTransferPayment',
                ]),
            ],
            new SchemaRegistry([
                'file:///schemas/CreditCardPayment.json'   => new Schema(type: SchemaType::Object),
                'file:///schemas/BankTransferPayment.json' => new Schema(type: SchemaType::Object),
            ]),
        ];

        // Optional / Undefined
        yield 'optional string' => [
            new Schema(type: SchemaType::String, required: false),
            [new RenderedType('string'), $undefined],
            $emptyRegistry,
        ];
        yield 'optional ref to object' => [
            new Schema(
                required: false,
                ref: new Ref(new AbsoluteUri('file:///schemas/Address.json')),
                xAlias: 'HomeAddress',
            ),
            [
                new RenderedType('HomeAddress', ['alias' => 'HomeAddress', 'fqcn' => 'App\\Dto\\Address']),
                $undefined,
            ],
            $addressRegistry,
        ];
        yield 'optional multi-type union without null' => [
            new Schema(type: [SchemaType::Integer, SchemaType::String], required: false),
            [new RenderedType('integer'), new RenderedType('string'), $undefined],
            $emptyRegistry,
        ];
        yield 'optional with default does not add Undefined' => [
            new Schema(type: SchemaType::String, required: false, default: new DefaultValue('foo')),
            [new RenderedType('string')],
            $emptyRegistry,
        ];
    }

    /**
     * @param list<RenderedType> $expected
     */
    #[DataProvider('provideCases')]
    public function testRender(Schema $schema, array $expected, SchemaRegistry $registry): void
    {
        self::assertEquals($expected, self::makeRenderer($registry)->render($schema));
    }
}
