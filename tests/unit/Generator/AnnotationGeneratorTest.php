<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\DefaultAnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\ResolvedAnnotation;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\DefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Undefined;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AnnotationGeneratorTest extends TestCase
{
    private static function make(SchemaRegistry $registry = new SchemaRegistry([])): DefaultAnnotationGenerator
    {
        return new DefaultAnnotationGenerator(
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
            $registry,
        );
    }

    /**
     * @return iterable<string, array{Schema, ResolvedAnnotation, SchemaRegistry}>
     */
    public static function provideCases(): iterable
    {
        $emptyRegistry = new SchemaRegistry([]);

        // Primitives
        yield 'string' => [
            new Schema(type: SchemaType::String),
            new ResolvedAnnotation('string', []),
            $emptyRegistry,
        ];
        yield 'non-empty-string' => [
            new Schema(type: SchemaType::String, minLength: 1),
            new ResolvedAnnotation('non-empty-string', []),
            $emptyRegistry,
        ];
        yield 'string minLength=0' => [
            new Schema(type: SchemaType::String, minLength: 0),
            new ResolvedAnnotation('string', []),
            $emptyRegistry,
        ];
        yield 'int' => [
            new Schema(type: SchemaType::Integer),
            new ResolvedAnnotation('int', []),
            $emptyRegistry,
        ];
        yield 'int min' => [
            new Schema(type: SchemaType::Integer, minimum: 5),
            new ResolvedAnnotation('int<5, max>', []),
            $emptyRegistry,
        ];
        yield 'int max' => [
            new Schema(type: SchemaType::Integer, maximum: 10),
            new ResolvedAnnotation('int<min, 10>', []),
            $emptyRegistry,
        ];
        yield 'int bounded' => [
            new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100),
            new ResolvedAnnotation('int<1, 100>', []),
            $emptyRegistry,
        ];
        yield 'int zero min' => [
            new Schema(type: SchemaType::Integer, minimum: 0),
            new ResolvedAnnotation('int<0, max>', []),
            $emptyRegistry,
        ];
        yield 'int negative' => [
            new Schema(type: SchemaType::Integer, minimum: -100, maximum: -1),
            new ResolvedAnnotation('int<-100, -1>', []),
            $emptyRegistry,
        ];
        yield 'float'  => [new Schema(type: SchemaType::Number), new ResolvedAnnotation('float', []), $emptyRegistry];
        yield 'bool'   => [new Schema(type: SchemaType::Boolean), new ResolvedAnnotation('bool', []), $emptyRegistry];
        yield 'null'   => [new Schema(type: SchemaType::Null), new ResolvedAnnotation('null', []), $emptyRegistry];
        yield 'object' => [new Schema(type: SchemaType::Object), new ResolvedAnnotation('object', []), $emptyRegistry];
        yield 'mixed'  => [new Schema(), new ResolvedAnnotation('mixed', []), $emptyRegistry];

        // Lists
        yield 'list of mixed' => [
            new Schema(type: SchemaType::Array),
            new ResolvedAnnotation('list<mixed>', []),
            $emptyRegistry,
        ];
        yield 'non-empty-list of mixed' => [
            new Schema(type: SchemaType::Array, minItems: 1),
            new ResolvedAnnotation('non-empty-list<mixed>', []),
            $emptyRegistry,
        ];
        yield 'list of string' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String)),
            new ResolvedAnnotation('list<string>', []),
            $emptyRegistry,
        ];
        yield 'list of int' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::Integer)),
            new ResolvedAnnotation('list<int>', []),
            $emptyRegistry,
        ];
        yield 'list of object' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::Object)),
            new ResolvedAnnotation('list<object>', []),
            $emptyRegistry,
        ];
        yield 'non-empty-list of string' => [
            new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 1),
            new ResolvedAnnotation('non-empty-list<string>', []),
            $emptyRegistry,
        ];

        // Enums
        yield 'enum of strings' => [
            new Schema(enum: ['foo', 'bar']),
            new ResolvedAnnotation("'foo'|'bar'", []),
            $emptyRegistry,
        ];
        yield 'enum of ints' => [
            new Schema(enum: [1, 2]),
            new ResolvedAnnotation('1|2', []),
            $emptyRegistry,
        ];
        yield 'enum of mixed' => [
            new Schema(enum: ['foo', 1]),
            new ResolvedAnnotation("'foo'|1", []),
            $emptyRegistry,
        ];
        yield 'enum single value' => [
            new Schema(enum: ['pending']),
            new ResolvedAnnotation("'pending'", []),
            $emptyRegistry,
        ];
        yield 'enum three ints' => [
            new Schema(enum: [1, 2, 3]),
            new ResolvedAnnotation('1|2|3', []),
            $emptyRegistry,
        ];

        // Unions
        yield 'anyOf string and null' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String),
                new Schema(type: SchemaType::Null),
            ]),
            new ResolvedAnnotation('string|null', []),
            $emptyRegistry,
        ];
        yield 'anyOf null and string (null hoisted)' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::Null),
                new Schema(type: SchemaType::String),
            ]),
            new ResolvedAnnotation('string|null', []),
            $emptyRegistry,
        ];
        yield 'anyOf string int' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String),
                new Schema(type: SchemaType::Integer),
            ]),
            new ResolvedAnnotation('string|int', []),
            $emptyRegistry,
        ];
        yield 'anyOf nullable non-empty-string' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String, minLength: 1),
                new Schema(type: SchemaType::Null),
            ]),
            new ResolvedAnnotation('non-empty-string|null', []),
            $emptyRegistry,
        ];
        yield 'anyOf string and bool' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::String),
                new Schema(type: SchemaType::Boolean),
            ]),
            new ResolvedAnnotation('string|bool', []),
            $emptyRegistry,
        ];
        yield 'anyOf nullable non-empty list' => [
            new Schema(anyOf: [
                new Schema(type: SchemaType::Array, items: new Schema(type: SchemaType::String), minItems: 1),
                new Schema(type: SchemaType::Null),
            ]),
            new ResolvedAnnotation('non-empty-list<string>|null', []),
            $emptyRegistry,
        ];
        yield 'type array string and null' => [
            new Schema(type: [SchemaType::String, SchemaType::Null]),
            new ResolvedAnnotation('string|null', []),
            $emptyRegistry,
        ];

        // Class refs
        $addressRegistry = new SchemaRegistry([
            'file:///schemas/Address.json' => new Schema(type: SchemaType::Object),
        ]);
        yield 'class ref' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Address.json'))),
            new ResolvedAnnotation('Address', [['alias' => 'Address', 'fqcn' => 'App\\Dto\\Address']]),
            $addressRegistry,
        ];
        yield 'class ref with alias' => [
            new Schema(
                ref: new Ref(new AbsoluteUri('file:///schemas/Address.json')),
                xAlias: 'BillingAddress',
            ),
            new ResolvedAnnotation(
                'BillingAddress',
                [['alias' => 'BillingAddress', 'fqcn' => 'App\\Dto\\Address']],
            ),
            $addressRegistry,
        ];
        yield 'anyOf ref and null' => [
            new Schema(anyOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Person.json'))),
                new Schema(type: SchemaType::Null),
            ]),
            new ResolvedAnnotation('Person|null', [['alias' => 'Person', 'fqcn' => 'App\\Dto\\Person']]),
            new SchemaRegistry([
                'file:///schemas/Person.json' => new Schema(type: SchemaType::Object),
            ]),
        ];
        yield 'anyOf two refs' => [
            new Schema(anyOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cat.json'))),
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Dog.json'))),
            ]),
            new ResolvedAnnotation('Cat|Dog', [
                ['alias' => 'Cat', 'fqcn' => 'App\\Dto\\Cat'],
                ['alias' => 'Dog', 'fqcn' => 'App\\Dto\\Dog'],
            ]),
            new SchemaRegistry([
                'file:///schemas/Cat.json' => new Schema(type: SchemaType::Object),
                'file:///schemas/Dog.json' => new Schema(type: SchemaType::Object),
            ]),
        ];

        // Optional / Undefined
        yield 'optional string adds Undefined' => [
            new Schema(type: SchemaType::String, required: false),
            new ResolvedAnnotation(
                'string|Undefined',
                [['alias' => 'Undefined', 'fqcn' => Undefined::class]],
            ),
            $emptyRegistry,
        ];
        yield 'optional mixed does not add Undefined' => [
            new Schema(required: false),
            new ResolvedAnnotation('mixed', []),
            $emptyRegistry,
        ];
        yield 'optional with default does not add Undefined' => [
            new Schema(type: SchemaType::String, required: false, default: new DefaultValue('foo')),
            new ResolvedAnnotation('string', []),
            $emptyRegistry,
        ];
    }

    #[DataProvider('provideCases')]
    public function testGenerate(Schema $schema, ResolvedAnnotation $expected, SchemaRegistry $registry): void
    {
        self::assertEquals($expected, self::make($registry)->generate($schema));
    }
}
