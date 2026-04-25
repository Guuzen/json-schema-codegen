<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\ClassResolvedType;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\ScalarResolvedType;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
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

    /**
     * @return iterable<string, array{Schema, ScalarResolvedType|ClassResolvedType|null}>
     */
    public static function provideTypes(): iterable
    {
        yield 'string' => [
            new Schema(type: SchemaType::String),
            new ScalarResolvedType('string'),
        ];
        yield 'nullable string from type array' => [
            new Schema(type: [SchemaType::String, SchemaType::Null]),
            new ScalarResolvedType('string'),
        ];
        yield 'nullable string from oneOf' => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Null)]),
            new ScalarResolvedType('string'),
        ];
        yield 'ref to integer' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Quantity.json'))),
            new ScalarResolvedType('integer'),
        ];
        yield 'nullable ref from oneOf' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Quantity.json'))),
                new Schema(type: SchemaType::Null),
            ]),
            new ScalarResolvedType('integer'),
        ];
        yield 'ref to object' => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/address/Address.json')), title: 'HomeAddress'),
            new ClassResolvedType('App\\Dto\\address\\Address', 'HomeAddress'),
        ];
        yield 'nullable ref to object from oneOf' => [
            new Schema(oneOf: [
                new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/address/Address.json')), title: 'HomeAddress'),
                new Schema(type: SchemaType::Null),
            ]),
            new ClassResolvedType('App\\Dto\\address\\Address', 'HomeAddress'),
        ];
        yield 'integer' => [
            new Schema(type: SchemaType::Integer),
            new ScalarResolvedType('integer'),
        ];
        yield 'number' => [
            new Schema(type: SchemaType::Number),
            new ScalarResolvedType('float'),
        ];
        yield 'boolean' => [
            new Schema(type: SchemaType::Boolean),
            new ScalarResolvedType('bool'),
        ];
        yield 'array' => [
            new Schema(type: SchemaType::Array),
            new ScalarResolvedType('array'),
        ];
        yield 'object' => [
            new Schema(type: SchemaType::Object),
            new ScalarResolvedType('object'),
        ];
        yield 'null' => [
            new Schema(type: SchemaType::Null),
            null,
        ];
        yield 'multi type union' => [
            new Schema(type: [SchemaType::Integer, SchemaType::String]),
            null,
        ];
        yield 'multi branch oneOf' => [
            new Schema(oneOf: [new Schema(type: SchemaType::Integer), new Schema(type: SchemaType::String)]),
            null,
        ];
        yield 'unknown' => [
            new Schema(),
            null,
        ];
    }

    #[DataProvider('provideTypes')]
    public function testGenerate(Schema $schema, ScalarResolvedType|ClassResolvedType|null $expected): void
    {
        $registry = new SchemaRegistry([
            'file:///schemas/Quantity.json' => new Schema(type: SchemaType::Integer, minimum: 1, maximum: 100),
            'file:///schemas/address/Address.json' => new Schema(type: SchemaType::Object),
        ]);

        self::assertEquals($expected, self::makeGenerator($registry)->generate($schema));
    }
}
