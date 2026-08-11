<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\AnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\DefaultAnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AnnotationGeneratorTest extends TestCase
{
    private static function make(): AnnotationGenerator
    {
        return new DefaultAnnotationGenerator(
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
        );
    }

    /**
     * @return iterable<string, array{Schema, string}>
     */
    public static function provideCases(): iterable
    {
        // Primitives
        yield 'string' => [
            new Schema(type: SchemaType::String),
            'string',
        ];
        yield 'non-empty-string' => [
            new Schema(type: SchemaType::String, minLength: 1),
            'non-empty-string',
        ];
        yield 'string minLength=0' => [
            new Schema(type: SchemaType::String, minLength: 0),
            'string',
        ];
        yield 'int' => [
            new Schema(type: SchemaType::Integer),
            'int',
        ];
        yield 'int min' => [
            new Schema(type: SchemaType::Integer, minimum: 5),
            'int<5, max>',
        ];
        yield 'int max' => [
            new Schema(type: SchemaType::Integer, maximum: 10),
            'int<min, 10>',
        ];
        yield 'int bounded' => [
            new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100),
            'int<1, 100>',
        ];
        yield 'int zero min' => [
            new Schema(type: SchemaType::Integer, minimum: 0),
            'int<0, max>',
        ];
        yield 'int negative' => [
            new Schema(type: SchemaType::Integer, minimum: -100, maximum: -1),
            'int<-100, -1>',
        ];
        yield 'float'  => [new Schema(type: SchemaType::Number), 'float'];
        yield 'bool'   => [new Schema(type: SchemaType::Boolean), 'bool'];
        yield 'null'   => [new Schema(type: SchemaType::Null), 'null'];
        yield 'object' => [new Schema(type: SchemaType::Object), 'object'];
        yield 'mixed'  => [new Schema(), 'mixed'];

        // Lists
        yield 'list of mixed' => [
            new Schema(type: SchemaType::Array),
            'list<mixed>',
        ];
        yield 'non-empty-list of mixed' => [
            new Schema(type: SchemaType::Array, minItems: 1),
            'non-empty-list<mixed>',
        ];
        yield 'list of string' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String)),
            'list<string>',
        ];
        yield 'list of int' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::Integer)),
            'list<int>',
        ];
        yield 'list of object' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::Object)),
            'list<object>',
        ];
        yield 'non-empty-list of string' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 1),
            'non-empty-list<string>',
        ];
        yield 'list of refs' => [
            new Schema(
                type: SchemaType::Array,
                items: new Schema(
                    ref: new Ref(new AbsoluteUri('file:///schemas/Address.json'))
                ),
            ),
            'list<Address>',
        ];

        // Enums
        yield 'enum of strings' => [
            new Schema(enum: ['foo', 'bar']),
            "'foo'|'bar'",
        ];
        yield 'enum of ints' => [
            new Schema(enum: [1, 2]),
            '1|2',
        ];
        yield 'enum of mixed' => [
            new Schema(enum: ['foo', 1]),
            "'foo'|1",
        ];
        yield 'enum single value' => [
            new Schema(enum: ['pending']),
            "'pending'",
        ];
        yield 'enum three ints' => [
            new Schema(enum: [1, 2, 3]),
            '1|2|3',
        ];
        yield 'enum empty' => [
            new Schema(enum: []),
            'mixed',
        ];

        // Unions
        yield 'anyOf string and null' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String),
                new Schema(type: SchemaType::Null),
            ]),
            'string|null',
        ];
        yield 'anyOf null and string (null is not hoisted)' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::Null),
                new Schema(type: SchemaType::String),
            ]),
            'null|string',
        ];
        yield 'anyOf string int' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String),
                new Schema(type: SchemaType::Integer),
            ]),
            'string|int',
        ];
        yield 'anyOf nullable non-empty-string' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String, minLength: 1),
                new Schema(type: SchemaType::Null),
            ]),
            'non-empty-string|null',
        ];
        yield 'anyOf string and bool' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String),
                new Schema(type: SchemaType::Boolean),
            ]),
            'string|bool',
        ];
        yield 'anyOf nullable non-empty list' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 1),
                new Schema(type: SchemaType::Null),
            ]),
            'non-empty-list<string>|null',
        ];
        yield 'type array string and null' => [
            new Schema(type: [SchemaType::String, SchemaType::Null]),
            'string|null',
        ];
        yield 'any of 2 string does not produce double string type' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String),
                new Schema(type: SchemaType::String),
            ]),
            'string',
        ];

        // Class refs
        yield 'class ref' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Address.json'))),
            'Address',
        ];
        yield 'anyOf ref and null' => [
            new Schema(anyOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Person.json'))),
                new Schema(type: SchemaType::Null),
            ]),
            'Person|null',
        ];
        yield 'anyOf two refs' => [
            new Schema(anyOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cat.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Dog.json'))),
            ]),
            'Cat|Dog',
        ];
        yield 'ref intersected with local constraint' => [
            new Schema(
                type: SchemaType::Integer,
                ref: new Ref(new AbsoluteUri('file:///schemas/Quantity.json')),
                maximum: 50,
            ),
            'Quantity&int<min, 50>',
        ];
    }

    #[DataProvider('provideCases')]
    public function testGenerate(Schema $schema, string $expected): void
    {
        self::assertEquals($expected, self::make()->generate($schema, new ClassNameRefNames()));
    }

    public function testRendersMappedTypeForAReferencedSchema(): void
    {
        $uri = 'file:///schemas/DateTimeImmutable.json';
        $generator = new DefaultAnnotationGenerator(
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
        );

        self::assertEquals(
            'DateTimeImmutable',
            $generator->generate(new Schema(ref: new Ref(new AbsoluteUri($uri))), new ClassNameRefNames()),
        );
    }
}
