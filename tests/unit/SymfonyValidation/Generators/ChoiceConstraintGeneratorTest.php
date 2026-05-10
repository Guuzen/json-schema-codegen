<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Generator\SchemaResolver;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\ChoiceConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\GreaterThanOrEqualConstraintGenerator;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

final class ChoiceConstraintGeneratorTest extends TestCase
{
    private static function makeChoice(SchemaRegistry $registry = new SchemaRegistry([])): ChoiceConstraintGenerator
    {
        return new ChoiceConstraintGenerator(new SchemaResolver($registry));
    }

    /**
     * @return iterable<string, array{Schema, ?Constraint, SchemaRegistry}>
     */
    public static function provideCases(): iterable
    {
        $emptyRegistry = new SchemaRegistry([]);
        $choice        = new Constraint(Choice::class, ['choices' => ['a', 'b']]);
        $enum          = new Schema(enum: ['a', 'b']);

        yield 'enum literal'           => [$enum, $choice, $emptyRegistry];
        yield 'nullable enum (oneOf)'  => [
            new Schema(oneOf: [$enum, new Schema(type: SchemaType::Null)]),
            $choice,
            $emptyRegistry,
        ];
        yield 'optional enum'          => [new Schema(enum: ['a', 'b'], required: false), $choice, $emptyRegistry];
        yield 'enum via ref'           => [
            new Schema(ref: new Ref(new AbsoluteUri('file:///schemas/Status.json'))),
            $choice,
            new SchemaRegistry(['file:///schemas/Status.json' => $enum]),
        ];

        yield 'string'             => [new Schema(type: SchemaType::String), null, $emptyRegistry];
        yield 'int'                => [new Schema(type: SchemaType::Integer), null, $emptyRegistry];
        yield 'bool'               => [new Schema(type: SchemaType::Boolean), null, $emptyRegistry];
        yield 'oneOf without enum' => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Integer)]),
            null,
            $emptyRegistry,
        ];
        yield 'nullable string'    => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Null)]),
            null,
            $emptyRegistry,
        ];
    }

    #[DataProvider('provideCases')]
    public function testGenerate(Schema $schema, ?Constraint $expected, SchemaRegistry $registry): void
    {
        self::assertEquals($expected, self::makeChoice($registry)->generate($schema));
    }

    public function testGreaterThanOrEqualOnNullableInt(): void
    {
        $generator = new GreaterThanOrEqualConstraintGenerator(new SchemaResolver(new SchemaRegistry([])));
        $schema = new Schema(oneOf: [
            new Schema(type: SchemaType::Integer, minimum: 0),
            new Schema(type: SchemaType::Null),
        ]);

        self::assertEquals(
            new Constraint(GreaterThanOrEqual::class, [0]),
            $generator->generate($schema),
        );
    }
}
