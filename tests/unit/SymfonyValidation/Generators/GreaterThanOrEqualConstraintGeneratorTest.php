<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\GreaterThanOrEqualConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\SchemaWalker;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

final class GreaterThanOrEqualConstraintGeneratorTest extends TestCase
{
    /**
     * @return iterable<array{schema: Schema, registry: SchemaRegistry, expected: list<Constraint>}>
     */
    public static function generationCases(): iterable
    {
        yield 'type contains integer' => [
            'schema' => new Schema(type: [SchemaType::Integer, SchemaType::String], minimum: 0),
            'registry' => new SchemaRegistry([]),
            'expected' => [new Constraint(name: GreaterThanOrEqual::class, args: [0])],
        ];

        yield 'oneOf integer zero minimum or null' => [
            'schema' => new Schema(oneOf: [
                new Schema(type: SchemaType::Integer, minimum: 0),
                new Schema(type: SchemaType::Null),
            ]),
            'registry' => new SchemaRegistry([]),
            'expected' => [new Constraint(name: GreaterThanOrEqual::class, args: [0])],
        ];

        yield 'oneOf and root constraint' => [
            'schema' => new Schema(
                type: SchemaType::Integer,
                oneOf: [
                    new Schema(type: SchemaType::Integer, minimum: 1),
                    new Schema(type: SchemaType::Null),
                ],
                minimum: 0
            ),
            'registry' => new SchemaRegistry([]),
            'expected' => [
                new Constraint(name: GreaterThanOrEqual::class, args: [0]),
                new Constraint(name: GreaterThanOrEqual::class, args: [1]),
            ],
        ];

        yield 'root constraint and ref constraint' => [
            'schema' => new Schema(
                type: SchemaType::Integer,
                ref: new Ref(uri: new AbsoluteUri('file:///schemas/Cent.json')),
                minimum: 0
            ),
            'registry' => new SchemaRegistry([
                'file:///schemas/Cent.json' => new Schema(type: SchemaType::Integer, minimum: 1),
            ]),
            'expected' => [
                new Constraint(name: GreaterThanOrEqual::class, args: [0]),
                new Constraint(name: GreaterThanOrEqual::class, args: [1]),
            ],
        ];

        yield 'root constraint and ref constraint with oneOf' => [
            'schema' => new Schema(
                type: SchemaType::Integer,
                ref: new Ref(
                    uri: new AbsoluteUri('file:///schemas/Cent.json'),
                ),
                minimum: 0
            ),
            'registry' => new SchemaRegistry([
                'file:///schemas/Cent.json' => new Schema(
                    type: SchemaType::Integer,
                    oneOf: [
                        new Schema(type: SchemaType::Integer, minimum: 2),
                        new Schema(type: SchemaType::Null),
                    ],
                    minimum: 1,
                ),
            ]),
            'expected' => [
                new Constraint(name: GreaterThanOrEqual::class, args: [0]),
                new Constraint(name: GreaterThanOrEqual::class, args: [1]),
                new Constraint(name: GreaterThanOrEqual::class, args: [2]),
            ],
        ];

        yield 'oneOf ref or null' => [
            'schema' => new Schema(
                oneOf: [
                    new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Cent.json'))),
                    new Schema(type: SchemaType::Null),
                ],
            ),
            'registry' => new SchemaRegistry([
                'file:///schemas/Cent.json' => new Schema(type: SchemaType::Integer, minimum: 0),
            ]),
            'expected' => [
                new Constraint(name: GreaterThanOrEqual::class, args: [0]),
            ]
        ];
    }

    /**
     * @param list<Constraint> $expected
     */
    #[DataProvider('generationCases')]
    public function testGeneration(Schema $schema, SchemaRegistry $registry, array $expected): void
    {
        $generator = new GreaterThanOrEqualConstraintGenerator(new SchemaWalker($registry));

        self::assertEquals(
            $expected,
            $generator->generate($schema),
        );
    }
}
