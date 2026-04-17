<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\AnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\DefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AnnotationGeneratorTest extends TestCase
{
    private static function makeGenerator(SchemaRegistry $registry = new SchemaRegistry([])): AnnotationGenerator
    {
        return AnnotationGenerator::default(
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
            $registry,
        );
    }

    public function testNoMatchingResolverReturnsMixed(): void
    {
        self::assertSame('mixed', self::makeGenerator()->generate(new Schema())->annotation);
    }

    public function testNoMatchingResolverWithDefaultValueReturnsMixed(): void
    {
        self::assertSame(
            'mixed',
            self::makeGenerator()->generate(new Schema(default: new DefaultValue('some')))->annotation,
        );
    }

    public function testObjectAnnotation(): void
    {
        $result = self::makeGenerator()->generate(new Schema(type: SchemaType::Object));

        self::assertSame('object', $result->annotation);
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideArrayAnnotations(): iterable
    {
        yield 'list without item type' => [new Schema(type: SchemaType::Array), 'list<mixed>'];
        yield 'list without item type with default value' => [
            new Schema(type: SchemaType::Array, default: new DefaultValue([])),
            'list<mixed>',
        ];
        yield 'non-empty-list without item type' => [
            new Schema(type: SchemaType::Array, minItems: 1),
            'non-empty-list<mixed>',
        ];
        yield 'list with string items' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String)),
            'list<string>',
        ];
        yield 'list with integer items' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::Integer)),
            'list<int>',
        ];
        yield 'list with object items' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::Object)),
            'list<object>',
        ];
        yield 'list with item type and minItems=0' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 0),
            'list<string>',
        ];
        yield 'non-empty-list with string items' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 1),
            'non-empty-list<string>',
        ];
        yield 'list with maxItems only' => [new Schema(type: SchemaType::Array, maxItems: 10), 'list<mixed>'];
        yield 'list with ref items' => [
            new Schema(
                type: SchemaType::Array,
                items: new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Tag.json'))),
            ),
            'list<Tag>',
        ];
        yield 'non-empty-list with ref items' => [
            new Schema(
                type: SchemaType::Array,
                items: new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Tag.json'))),
                minItems: 1,
            ),
            'non-empty-list<Tag>',
        ];
    }

    #[DataProvider('provideArrayAnnotations')]
    public function testArrayAnnotation(Schema $schema, string $expected): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Tag.json' => new Schema(type: SchemaType::Object),
        ]);

        self::assertSame($expected, self::makeGenerator($registry)->generate($schema)->annotation);
    }

    /**
     * @return iterable<string, array{Schema, list<array{alias: string, fqcn: string}>}>
     */
    public static function provideArrayImports(): iterable
    {
        yield 'list with ref items' => [
            new Schema(
                type: SchemaType::Array,
                items: new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Tag.json'))),
            ),
            [['alias' => 'Tag', 'fqcn' => 'App\\Dto\\Tag']],
        ];
        yield 'non-empty-list with ref items' => [
            new Schema(
                type: SchemaType::Array,
                items: new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Tag.json'))),
                minItems: 1,
            ),
            [['alias' => 'Tag', 'fqcn' => 'App\\Dto\\Tag']],
        ];
    }

    /**
     * @param list<array{alias: string, fqcn: string}> $expected
     */
    #[DataProvider('provideArrayImports')]
    public function testArrayImports(Schema $schema, array $expected): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Tag.json' => new Schema(type: SchemaType::Object),
        ]);

        self::assertSame($expected, self::makeGenerator($registry)->generate($schema)->imports);
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideUnionAnnotations(): iterable
    {
        // --- oneOf ---
        yield 'oneOf with single string branch' => [
            new Schema(oneOf: [new Schema(type: SchemaType::String)]),
            'string',
        ];
        yield 'oneOf with single null branch' => [new Schema(oneOf: [new Schema(type: SchemaType::Null)]), 'null'];
        yield 'oneOf with string and null' => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Null)]),
            'string|null',
        ];
        yield 'oneOf with null and string' => [
            new Schema(oneOf: [new Schema(type: SchemaType::Null), new Schema(type: SchemaType::String)]),
            'string|null',
        ];
        yield 'oneOf with string and integer' => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Integer)]),
            'string|int',
        ];
        yield 'oneOf with nullable non-empty-string' => [
            new Schema(oneOf: [new Schema(type: SchemaType::Null), new Schema(type: SchemaType::String, minLength: 1)]),
            'non-empty-string|null',
        ];
        yield 'oneOf with string and bool' => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Boolean)]),
            'string|bool',
        ];
        yield 'oneOf nullable non-empty-list' => [
            new Schema(oneOf: [
                new Schema(type: SchemaType::Null),
                new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 1),
            ]),
            'non-empty-list<string>|null',
        ];
        yield 'oneOf with empty array → mixed' => [new Schema(oneOf: []), 'mixed'];
        yield 'oneOf with ref and null' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Person.json'))),
                new Schema(type: SchemaType::Null),
            ]),
            'Person|null',
        ];
        yield 'oneOf with two refs' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cat.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Dog.json'))),
            ]),
            'Cat|Dog',
        ];
        yield 'oneOf with three refs' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cat.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Dog.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Bird.json'))),
            ]),
            'Cat|Dog|Bird',
        ];

        // --- type arrays ---
        yield 'type array with single string' => [new Schema(type: [SchemaType::String]), 'string'];
        yield 'type array with string and null' => [
            new Schema(type: [SchemaType::String, SchemaType::Null]),
            'string|null',
        ];
        yield 'type array with null and string' => [
            new Schema(type: [SchemaType::Null, SchemaType::String]),
            'string|null',
        ];
        yield 'type array with integer and null' => [
            new Schema(type: [SchemaType::Integer, SchemaType::Null]),
            'int|null',
        ];
        yield 'type array with boolean and null' => [
            new Schema(type: [SchemaType::Boolean, SchemaType::Null]),
            'bool|null',
        ];
        yield 'type array with object and null' => [
            new Schema(type: [SchemaType::Object, SchemaType::Null]),
            'object|null',
        ];
        yield 'type array with array type and null' => [
            new Schema(type: [SchemaType::Array, SchemaType::Null]),
            'list<mixed>|null',
        ];
        yield 'type array with string and integer' => [
            new Schema(type: [SchemaType::String, SchemaType::Integer]),
            'string|int',
        ];
        yield 'type array with only null' => [new Schema(type: [SchemaType::Null]), 'null'];
        yield 'type array with integer string and null' => [
            new Schema(type: [SchemaType::Integer, SchemaType::String, SchemaType::Null]),
            'int|string|null',
        ];
        yield 'union nullable list' => [
            new Schema(type: [SchemaType::Array, SchemaType::Null]),
            'list<mixed>|null',
        ];
    }

    #[DataProvider('provideUnionAnnotations')]
    public function testUnionAnnotation(Schema $schema, string $expected): void
    {
        $objectSchema = new Schema(type: SchemaType::Object);
        $registry = new SchemaRegistry([
            'file:///schemas/Person.json' => $objectSchema,
            'file:///schemas/Cat.json'    => $objectSchema,
            'file:///schemas/Dog.json'    => $objectSchema,
            'file:///schemas/Bird.json'   => $objectSchema,
        ]);

        self::assertSame($expected, self::makeGenerator($registry)->generate($schema)->annotation);
    }

    /**
     * @return iterable<string, array{Schema, list<array{alias: string, fqcn: string}>}>
     */
    public static function provideUnionImports(): iterable
    {
        yield 'oneOf with ref and null' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Person.json'))),
                new Schema(type: SchemaType::Null),
            ]),
            [['alias' => 'Person', 'fqcn' => 'App\\Dto\\Person']],
        ];
        yield 'oneOf with two refs' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cat.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Dog.json'))),
            ]),
            [
                ['alias' => 'Cat', 'fqcn' => 'App\\Dto\\Cat'],
                ['alias' => 'Dog', 'fqcn' => 'App\\Dto\\Dog'],
            ],
        ];
        yield 'oneOf with three refs' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cat.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Dog.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Bird.json'))),
            ]),
            [
                ['alias' => 'Cat', 'fqcn' => 'App\\Dto\\Cat'],
                ['alias' => 'Dog', 'fqcn' => 'App\\Dto\\Dog'],
                ['alias' => 'Bird', 'fqcn' => 'App\\Dto\\Bird'],
            ],
        ];
    }

    /**
     * @param list<array{alias: string, fqcn: string}> $expected
     */
    #[DataProvider('provideUnionImports')]
    public function testUnionImports(Schema $schema, array $expected): void
    {
        $objectSchema = new Schema(type: SchemaType::Object);
        $registry = new SchemaRegistry([
            'file:///schemas/Person.json' => $objectSchema,
            'file:///schemas/Cat.json'    => $objectSchema,
            'file:///schemas/Dog.json'    => $objectSchema,
            'file:///schemas/Bird.json'   => $objectSchema,
        ]);

        self::assertSame($expected, self::makeGenerator($registry)->generate($schema)->imports);
    }

    /**
     * @return iterable<string, array{Schema, string, list<array{alias: string, fqcn: string}>}>
     */
    public static function provideRefObjectAnnotations(): iterable
    {
        yield 'ref to object schema' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Address.json'))),
            'Address',
            [['alias' => 'Address', 'fqcn' => 'App\\Dto\\Address']],
        ];
        yield 'ref to object schema with title override' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Address.json')), title: 'BillingAddress'),
            'BillingAddress',
            [['alias' => 'BillingAddress', 'fqcn' => 'App\\Dto\\Address']],
        ];
    }

    /**
     * @param list<array{alias: string, fqcn: string}> $expectedImports
     */
    #[DataProvider('provideRefObjectAnnotations')]
    public function testRefObjectAnnotation(Schema $schema, string $expectedAnnotation, array $expectedImports): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Address.json' => new Schema(type: SchemaType::Object),
        ]);
        $result = self::makeGenerator($registry)->generate($schema);

        self::assertSame($expectedAnnotation, $result->annotation);
        self::assertSame($expectedImports, $result->imports);
    }

    /**
     * When a property schema has a $ref pointing to a standalone primitive/enum/array schema,
     * the annotation is inlined rather than resolved to a class name with an import.
     *
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideInlineRefAnnotations(): iterable
    {
        yield 'ref to string'              => [new Schema(type: SchemaType::String), 'string'];
        yield 'ref to non-empty-string'    => [new Schema(type: SchemaType::String, minLength: 1), 'non-empty-string'];
        yield 'ref to boolean'             => [new Schema(type: SchemaType::Boolean), 'bool'];
        yield 'ref to integer'             => [new Schema(type: SchemaType::Integer), 'int'];
        yield 'ref to ranged integer'      => [new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100), 'int<1, 100>'];
        yield 'ref to null'                => [new Schema(type: SchemaType::Null), 'null'];
        yield 'ref to number'              => [new Schema(type: SchemaType::Number), 'float'];
        yield 'ref to array'               => [new Schema(type: SchemaType::Array), 'list<mixed>'];
        yield 'ref to non-empty array'     => [new Schema(type: SchemaType::Array, minItems: 1), 'non-empty-list<mixed>'];
        yield 'ref to string enum'         => [new Schema(enum: ['foo', 'bar']), "'foo'|'bar'"];
        yield 'ref to integer enum'        => [new Schema(enum: [1, 2]), '1|2'];
        yield 'ref to mixed enum'          => [new Schema(enum: ['active', 1]), "'active'|1"];
        yield 'ref to single-value enum'   => [new Schema(enum: ['pending']), "'pending'"];
    }

    #[DataProvider('provideInlineRefAnnotations')]
    public function testInlineRefAnnotation(Schema $referencedSchema, string $expected): void
    {
        $refUri = new AbsoluteUri('file:///schemas/Inline.json');
        $registry = new SchemaRegistry([$refUri->value => $referencedSchema]);
        $result = self::makeGenerator($registry)->generate(new Schema(ref: new Ref($refUri)));

        self::assertSame($expected, $result->annotation);
        self::assertSame([], $result->imports);
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideStringAnnotations(): iterable
    {
        yield 'string without minLength'          => [new Schema(type: SchemaType::String), 'string'];
        yield 'string with minLength=0'           => [new Schema(type: SchemaType::String, minLength: 0), 'string'];
        yield 'string with minLength=1'           => [new Schema(type: SchemaType::String, minLength: 1), 'non-empty-string'];
        yield 'string with minLength=5'           => [new Schema(type: SchemaType::String, minLength: 5), 'non-empty-string'];
        yield 'string with maxLength'             => [new Schema(type: SchemaType::String, maxLength: 100), 'string'];
        yield 'string with minLength and maxLength' => [
            new Schema(type: SchemaType::String, minLength: 1, maxLength: 50),
            'non-empty-string',
        ];
        yield 'string with default value'         => [
            new Schema(type: SchemaType::String, default: new DefaultValue('foo')),
            'string',
        ];
    }

    #[DataProvider('provideStringAnnotations')]
    public function testStringAnnotation(Schema $schema, string $expected): void
    {
        self::assertSame($expected, self::makeGenerator()->generate($schema)->annotation);
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideIntegerAnnotations(): iterable
    {
        yield 'int without bounds'      => [new Schema(type: SchemaType::Integer), 'int'];
        yield 'int with minimum only'   => [new Schema(type: SchemaType::Integer, minimum: 5), 'int<5, max>'];
        yield 'int with maximum only'   => [new Schema(type: SchemaType::Integer, maximum: 10), 'int<min, 10>'];
        yield 'int with both bounds'    => [new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100), 'int<1, 100>'];
        yield 'int with zero minimum'   => [new Schema(type: SchemaType::Integer, minimum: 0), 'int<0, max>'];
        yield 'int with negative bounds' => [
            new Schema(type: SchemaType::Integer, minimum: -100, maximum: -1),
            'int<-100, -1>',
        ];
        yield 'int with default value'  => [
            new Schema(type: SchemaType::Integer, default: new DefaultValue(1)),
            'int',
        ];
    }

    #[DataProvider('provideIntegerAnnotations')]
    public function testIntegerAnnotation(Schema $schema, string $expected): void
    {
        self::assertSame($expected, self::makeGenerator()->generate($schema)->annotation);
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideNumberAnnotations(): iterable
    {
        yield 'float'                  => [new Schema(type: SchemaType::Number), 'float'];
        yield 'float with bounds'      => [new Schema(type: SchemaType::Number, minimum: 0, maximum: 100), 'float'];
        yield 'float with default value' => [
            new Schema(type: SchemaType::Number, default: new DefaultValue(1.1)),
            'float',
        ];
    }

    #[DataProvider('provideNumberAnnotations')]
    public function testNumberAnnotation(Schema $schema, string $expected): void
    {
        self::assertSame($expected, self::makeGenerator()->generate($schema)->annotation);
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideBooleanAnnotations(): iterable
    {
        yield 'bool'                  => [new Schema(type: SchemaType::Boolean), 'bool'];
        yield 'bool with default value' => [
            new Schema(type: SchemaType::Boolean, default: new DefaultValue(false)),
            'bool',
        ];
    }

    #[DataProvider('provideBooleanAnnotations')]
    public function testBooleanAnnotation(Schema $schema, string $expected): void
    {
        self::assertSame($expected, self::makeGenerator()->generate($schema)->annotation);
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideNullAnnotations(): iterable
    {
        yield 'null type'                  => [new Schema(type: SchemaType::Null), 'null'];
        yield 'null type with default value' => [
            new Schema(type: SchemaType::Null, default: new DefaultValue(null)),
            'null',
        ];
    }

    #[DataProvider('provideNullAnnotations')]
    public function testNullAnnotation(Schema $schema, string $expected): void
    {
        self::assertSame($expected, self::makeGenerator()->generate($schema)->annotation);
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideEnumAnnotations(): iterable
    {
        yield 'enum of strings'              => [new Schema(enum: ['foo', 'bar']), "'foo'|'bar'"];
        yield 'enum of ints'                 => [new Schema(enum: [1, 2]), '1|2'];
        yield 'enum of mixed'                => [new Schema(enum: ['foo', 1]), "'foo'|1"];
        yield 'enum of string values'        => [new Schema(enum: ['active', 'inactive']), "'active'|'inactive'"];
        yield 'enum of three ints'           => [new Schema(enum: [1, 2, 3]), '1|2|3'];
        yield 'enum mixed string and int'    => [new Schema(enum: ['active', 1]), "'active'|1"];
        yield 'enum strings with default value' => [
            new Schema(enum: ['active', 'disabled'], default: new DefaultValue('active')),
            "'active'|'disabled'",
        ];
    }

    #[DataProvider('provideEnumAnnotations')]
    public function testEnumAnnotation(Schema $schema, string $expected): void
    {
        self::assertSame($expected, self::makeGenerator()->generate($schema)->annotation);
    }
}
