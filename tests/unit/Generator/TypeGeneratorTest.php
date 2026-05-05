<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\BoolType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ClassRefType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\FloatType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\MixedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\NullType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ObjectType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UndefinedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\TypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\DefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeGeneratorTest extends TestCase
{
    private static function makeGenerator(SchemaRegistry $registry = new SchemaRegistry([])): TypeGenerator
    {
        return new TypeGenerator(
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
            $registry,
        );
    }

    public function testUnknownSchemaReturnsMixed(): void
    {
        self::assertEquals(new MixedType(), self::makeGenerator()->generate(new Schema()));
    }

    public function testUnknownSchemaWithDefaultReturnsMixed(): void
    {
        self::assertEquals(new MixedType(), self::makeGenerator()->generate(new Schema(default: new DefaultValue('some'))));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideStringTypes(): iterable
    {
        yield 'string'                       => [new Schema(type: SchemaType::String), new StringType()];
        yield 'non-empty string'             => [new Schema(type: SchemaType::String, minLength: 1), new StringType(nonEmpty: true)];
        yield 'string with minLength=0'      => [new Schema(type: SchemaType::String, minLength: 0), new StringType()];
        yield 'string with minLength=5'      => [new Schema(type: SchemaType::String, minLength: 5), new StringType(nonEmpty: true)];
        yield 'string with maxLength only'   => [new Schema(type: SchemaType::String, maxLength: 100), new StringType()];
        yield 'string with both lengths'     => [
            new Schema(type: SchemaType::String, minLength: 1, maxLength: 50),
            new StringType(nonEmpty: true),
        ];
        yield 'string with default value'    => [
            new Schema(type: SchemaType::String, default: new DefaultValue('foo')),
            new StringType(),
        ];
    }

    #[DataProvider('provideStringTypes')]
    public function testStringType(Schema $schema, PhpType $expected): void
    {
        self::assertEquals($expected, self::makeGenerator()->generate($schema));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideIntegerTypes(): iterable
    {
        yield 'int without bounds'       => [new Schema(type: SchemaType::Integer), new IntType()];
        yield 'int with minimum only'    => [new Schema(type: SchemaType::Integer, minimum: 5), new IntType(min: 5)];
        yield 'int with maximum only'    => [new Schema(type: SchemaType::Integer, maximum: 10), new IntType(max: 10)];
        yield 'int with both bounds'     => [new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100), new IntType(min: 1, max: 100)];
        yield 'int with zero minimum'    => [new Schema(type: SchemaType::Integer, minimum: 0), new IntType(min: 0)];
        yield 'int with negative bounds' => [
            new Schema(type: SchemaType::Integer, minimum: -100, maximum: -1),
            new IntType(min: -100, max: -1),
        ];
        yield 'int with default value'   => [
            new Schema(type: SchemaType::Integer, default: new DefaultValue(1)),
            new IntType(),
        ];
    }

    #[DataProvider('provideIntegerTypes')]
    public function testIntegerType(Schema $schema, PhpType $expected): void
    {
        self::assertEquals($expected, self::makeGenerator()->generate($schema));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideNumberTypes(): iterable
    {
        yield 'float'              => [new Schema(type: SchemaType::Number), new FloatType()];
        yield 'float with bounds'  => [new Schema(type: SchemaType::Number, minimum: 0, maximum: 100), new FloatType()];
        yield 'float with default' => [new Schema(type: SchemaType::Number, default: new DefaultValue(1.1)), new FloatType()];
    }

    #[DataProvider('provideNumberTypes')]
    public function testNumberType(Schema $schema, PhpType $expected): void
    {
        self::assertEquals($expected, self::makeGenerator()->generate($schema));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideBooleanTypes(): iterable
    {
        yield 'bool'              => [new Schema(type: SchemaType::Boolean), new BoolType()];
        yield 'bool with default' => [new Schema(type: SchemaType::Boolean, default: new DefaultValue(false)), new BoolType()];
    }

    #[DataProvider('provideBooleanTypes')]
    public function testBooleanType(Schema $schema, PhpType $expected): void
    {
        self::assertEquals($expected, self::makeGenerator()->generate($schema));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideNullTypes(): iterable
    {
        yield 'null'              => [new Schema(type: SchemaType::Null), new NullType()];
        yield 'null with default' => [new Schema(type: SchemaType::Null, default: new DefaultValue(null)), new NullType()];
    }

    #[DataProvider('provideNullTypes')]
    public function testNullType(Schema $schema, PhpType $expected): void
    {
        self::assertEquals($expected, self::makeGenerator()->generate($schema));
    }

    public function testObjectType(): void
    {
        self::assertEquals(new ObjectType(), self::makeGenerator()->generate(new Schema(type: SchemaType::Object)));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideArrayTypes(): iterable
    {
        yield 'list without items'             => [new Schema(type: SchemaType::Array), new ListType(new MixedType())];
        yield 'list with default value'        => [
            new Schema(type: SchemaType::Array, default: new DefaultValue([])),
            new ListType(new MixedType()),
        ];
        yield 'non-empty list without items'   => [new Schema(type: SchemaType::Array, minItems: 1), new ListType(new MixedType(), nonEmpty: true)];
        yield 'list with string items'         => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String)),
            new ListType(new StringType()),
        ];
        yield 'non-empty list with string items' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 1),
            new ListType(new StringType(), nonEmpty: true),
        ];
        yield 'list with integer items'        => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::Integer)),
            new ListType(new IntType()),
        ];
        yield 'list with object items'         => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::Object)),
            new ListType(new ObjectType()),
        ];
        yield 'list with minItems=0'           => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 0),
            new ListType(new StringType()),
        ];
        yield 'list with maxItems only'        => [new Schema(type: SchemaType::Array, maxItems: 10), new ListType(new MixedType())];
        yield 'list with ref items'            => [
            new Schema(
                type: SchemaType::Array,
                items: new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Tag.json'))),
            ),
            new ListType(new ClassRefType('Tag', 'App\\Dto\\Tag')),
        ];
        yield 'non-empty list with ref items'  => [
            new Schema(
                type: SchemaType::Array,
                items: new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Tag.json'))),
                minItems: 1,
            ),
            new ListType(new ClassRefType('Tag', 'App\\Dto\\Tag'), nonEmpty: true),
        ];
    }

    #[DataProvider('provideArrayTypes')]
    public function testArrayType(Schema $schema, PhpType $expected): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Tag.json' => new Schema(type: SchemaType::Object),
        ]);

        self::assertEquals($expected, self::makeGenerator($registry)->generate($schema));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideEnumTypes(): iterable
    {
        yield 'string enum'          => [new Schema(enum: ['foo', 'bar']), new EnumLiteralType(['foo', 'bar'])];
        yield 'integer enum'         => [new Schema(enum: [1, 2]), new EnumLiteralType([1, 2])];
        yield 'mixed enum'           => [new Schema(enum: ['active', 1]), new EnumLiteralType(['active', 1])];
        yield 'single string enum'   => [new Schema(enum: ['pending']), new EnumLiteralType(['pending'])];
        yield 'three int enum'       => [new Schema(enum: [1, 2, 3]), new EnumLiteralType([1, 2, 3])];
        yield 'enum with default'    => [
            new Schema(enum: ['active', 'inactive'], default: new DefaultValue('active')),
            new EnumLiteralType(['active', 'inactive']),
        ];
    }

    #[DataProvider('provideEnumTypes')]
    public function testEnumType(Schema $schema, PhpType $expected): void
    {
        self::assertEquals($expected, self::makeGenerator()->generate($schema));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideUnionTypes(): iterable
    {
        // --- oneOf ---
        yield 'oneOf single string branch'          => [
            new Schema(oneOf: [new Schema(type: SchemaType::String)]),
            new StringType(),
        ];
        yield 'oneOf single null branch'            => [
            new Schema(oneOf: [new Schema(type: SchemaType::Null)]),
            new NullType(),
        ];
        yield 'oneOf string and null'               => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Null)]),
            new UnionType([new StringType(), new NullType()]),
        ];
        yield 'oneOf null and string (null normalized to end)' => [
            new Schema(oneOf: [new Schema(type: SchemaType::Null), new Schema(type: SchemaType::String)]),
            new UnionType([new StringType(), new NullType()]),
        ];
        yield 'oneOf string and integer'            => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Integer)]),
            new UnionType([new StringType(), new IntType()]),
        ];
        yield 'oneOf nullable non-empty string'     => [
            new Schema(oneOf: [new Schema(type: SchemaType::Null), new Schema(type: SchemaType::String, minLength: 1)]),
            new UnionType([new StringType(nonEmpty: true), new NullType()]),
        ];
        yield 'oneOf string and bool'               => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Boolean)]),
            new UnionType([new StringType(), new BoolType()]),
        ];
        yield 'oneOf nullable non-empty list'       => [
            new Schema(oneOf: [
                new Schema(type: SchemaType::Null),
                new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 1),
            ]),
            new UnionType([new ListType(new StringType(), nonEmpty: true), new NullType()]),
        ];
        yield 'oneOf empty → mixed'                 => [new Schema(oneOf: []), new MixedType()];
        yield 'oneOf ref and null'                  => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Person.json'))),
                new Schema(type: SchemaType::Null),
            ]),
            new UnionType([new ClassRefType('Person', 'App\\Dto\\Person'), new NullType()]),
        ];
        yield 'oneOf two refs'                      => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cat.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Dog.json'))),
            ]),
            new UnionType([new ClassRefType('Cat', 'App\\Dto\\Cat'), new ClassRefType('Dog', 'App\\Dto\\Dog')]),
        ];
        yield 'oneOf three refs'                    => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cat.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Dog.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Bird.json'))),
            ]),
            new UnionType([
                new ClassRefType('Cat', 'App\\Dto\\Cat'),
                new ClassRefType('Dog', 'App\\Dto\\Dog'),
                new ClassRefType('Bird', 'App\\Dto\\Bird'),
            ]),
        ];

        // --- type arrays ---
        yield 'type array single string'            => [new Schema(type: [SchemaType::String]), new StringType()];
        yield 'type array only null'                => [new Schema(type: [SchemaType::Null]), new NullType()];
        yield 'type array string and null'          => [
            new Schema(type: [SchemaType::String, SchemaType::Null]),
            new UnionType([new StringType(), new NullType()]),
        ];
        yield 'type array null and string (null normalized to end)' => [
            new Schema(type: [SchemaType::Null, SchemaType::String]),
            new UnionType([new StringType(), new NullType()]),
        ];
        yield 'type array integer and null'         => [
            new Schema(type: [SchemaType::Integer, SchemaType::Null]),
            new UnionType([new IntType(), new NullType()]),
        ];
        yield 'type array boolean and null'         => [
            new Schema(type: [SchemaType::Boolean, SchemaType::Null]),
            new UnionType([new BoolType(), new NullType()]),
        ];
        yield 'type array object and null'          => [
            new Schema(type: [SchemaType::Object, SchemaType::Null]),
            new UnionType([new ObjectType(), new NullType()]),
        ];
        yield 'type array array type and null'      => [
            new Schema(type: [SchemaType::Array, SchemaType::Null]),
            new UnionType([new ListType(new MixedType()), new NullType()]),
        ];
        yield 'type array string and integer'       => [
            new Schema(type: [SchemaType::String, SchemaType::Integer]),
            new UnionType([new StringType(), new IntType()]),
        ];
        yield 'type array integer string and null'  => [
            new Schema(type: [SchemaType::Integer, SchemaType::String, SchemaType::Null]),
            new UnionType([new IntType(), new StringType(), new NullType()]),
        ];
    }

    #[DataProvider('provideUnionTypes')]
    public function testUnionType(Schema $schema, PhpType $expected): void
    {
        $objectSchema = new Schema(type: SchemaType::Object);
        $registry = new SchemaRegistry([
            'file:///schemas/Person.json' => $objectSchema,
            'file:///schemas/Cat.json'    => $objectSchema,
            'file:///schemas/Dog.json'    => $objectSchema,
            'file:///schemas/Bird.json'   => $objectSchema,
        ]);

        self::assertEquals($expected, self::makeGenerator($registry)->generate($schema));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideRefObjectTypes(): iterable
    {
        yield 'ref to object'                  => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Address.json'))),
            new ClassRefType('Address', 'App\\Dto\\Address'),
        ];
        yield 'ref to object with title override' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Address.json')), title: 'BillingAddress'),
            new ClassRefType('BillingAddress', 'App\\Dto\\Address'),
        ];
    }

    #[DataProvider('provideRefObjectTypes')]
    public function testRefObjectType(Schema $schema, PhpType $expected): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Address.json' => new Schema(type: SchemaType::Object),
        ]);

        self::assertEquals($expected, self::makeGenerator($registry)->generate($schema));
    }

    /**
     * Non-object $ref schemas are inlined — the referenced type is used directly.
     *
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideInlineRefTypes(): iterable
    {
        yield 'ref to string'           => [new Schema(type: SchemaType::String), new StringType()];
        yield 'ref to non-empty string' => [new Schema(type: SchemaType::String, minLength: 1), new StringType(nonEmpty: true)];
        yield 'ref to boolean'          => [new Schema(type: SchemaType::Boolean), new BoolType()];
        yield 'ref to integer'          => [new Schema(type: SchemaType::Integer), new IntType()];
        yield 'ref to ranged integer'   => [
            new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100),
            new IntType(min: 1, max: 100),
        ];
        yield 'ref to null'             => [new Schema(type: SchemaType::Null), new NullType()];
        yield 'ref to number'           => [new Schema(type: SchemaType::Number), new FloatType()];
        yield 'ref to array'            => [new Schema(type: SchemaType::Array), new ListType(new MixedType())];
        yield 'ref to non-empty array'  => [
            new Schema(type: SchemaType::Array, minItems: 1),
            new ListType(new MixedType(), nonEmpty: true),
        ];
        yield 'ref to string enum'      => [new Schema(enum: ['foo', 'bar']), new EnumLiteralType(['foo', 'bar'])];
        yield 'ref to integer enum'     => [new Schema(enum: [1, 2]), new EnumLiteralType([1, 2])];
        yield 'ref to mixed enum'       => [new Schema(enum: ['active', 1]), new EnumLiteralType(['active', 1])];
        yield 'ref to single-value enum' => [new Schema(enum: ['pending']), new EnumLiteralType(['pending'])];
    }

    #[DataProvider('provideInlineRefTypes')]
    public function testInlineRefType(Schema $referencedSchema, PhpType $expected): void
    {
        $refUri  = new AbsoluteUri('file:///schemas/Inline.json');
        $registry = new SchemaRegistry([$refUri->value => $referencedSchema]);

        self::assertEquals($expected, self::makeGenerator($registry)->generate(new Schema(ref: new Ref($refUri))));
    }

    /**
     * @return iterable<string, array{Schema, PhpType}>
     */
    public static function provideOptionalTypes(): iterable
    {
        yield 'optional string'                => [
            new Schema(type: SchemaType::String, required: false),
            new UnionType([new StringType(), new UndefinedType()]),
        ];
        yield 'optional string with default keeps original type' => [
            new Schema(type: SchemaType::String, required: false, default: new DefaultValue('foo')),
            new StringType(),
        ];
        yield 'optional integer'               => [
            new Schema(type: SchemaType::Integer, required: false),
            new UnionType([new IntType(), new UndefinedType()]),
        ];
        yield 'optional boolean'               => [
            new Schema(type: SchemaType::Boolean, required: false),
            new UnionType([new BoolType(), new UndefinedType()]),
        ];
        yield 'optional nullable string'       => [
            new Schema(type: [SchemaType::String, SchemaType::Null], required: false),
            new UnionType([new StringType(), new NullType(), new UndefinedType()]),
        ];
        yield 'optional null type'             => [
            new Schema(type: SchemaType::Null, required: false),
            new UnionType([new NullType(), new UndefinedType()]),
        ];
        yield 'optional enum'                  => [
            new Schema(required: false, enum: ['a', 'b']),
            new UnionType([new EnumLiteralType(['a', 'b']), new UndefinedType()]),
        ];
        yield 'optional unknown is still mixed' => [
            new Schema(required: false),
            new MixedType(),
        ];
        yield 'optional multi-type union without null' => [
            new Schema(type: [SchemaType::Integer, SchemaType::String], required: false),
            new UnionType([new IntType(), new StringType(), new UndefinedType()]),
        ];
    }

    #[DataProvider('provideOptionalTypes')]
    public function testOptionalType(Schema $schema, PhpType $expected): void
    {
        self::assertEquals($expected, self::makeGenerator()->generate($schema));
    }

    public function testOptionalRefToObject(): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Address.json' => new Schema(type: SchemaType::Object),
        ]);
        $schema = new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Address.json')), required: false);

        self::assertEquals(
            new UnionType([new ClassRefType('Address', 'App\\Dto\\Address'), new UndefinedType()]),
            self::makeGenerator($registry)->generate($schema),
        );
    }
}
