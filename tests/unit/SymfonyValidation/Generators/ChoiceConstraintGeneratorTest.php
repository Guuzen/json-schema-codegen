<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\SymfonyValidation\Generators;

use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Constraint;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\Generators\ChoiceConstraintGenerator;
use Guuzen\JsonSchemaCodegen\SymfonyValidation\SchemaWalker;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Choice;

final class ChoiceConstraintGeneratorTest extends TestCase
{
    private static function makeChoice(SchemaRegistry $registry = new SchemaRegistry([])): ChoiceConstraintGenerator
    {
        return new ChoiceConstraintGenerator(new SchemaWalker($registry));
    }

    /**
     * @return iterable<string, array{Schema, list<Constraint>, SchemaRegistry}>
     */
    public static function provideCases(): iterable
    {
        $emptyRegistry = new SchemaRegistry([]);
        $choice        = [new Constraint(Choice::class, ['choices' => ['a', 'b']])];
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

        yield 'string'             => [new Schema(type: SchemaType::String), [], $emptyRegistry];
        yield 'int'                => [new Schema(type: SchemaType::Integer), [], $emptyRegistry];
        yield 'bool'               => [new Schema(type: SchemaType::Boolean), [], $emptyRegistry];
        yield 'oneOf without enum' => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Integer)]),
            [],
            $emptyRegistry,
        ];
        yield 'nullable string'    => [
            new Schema(oneOf: [new Schema(type: SchemaType::String), new Schema(type: SchemaType::Null)]),
            [],
            $emptyRegistry,
        ];
    }

    /**
     * @param list<Constraint> $expected
     */
    #[DataProvider('provideCases')]
    public function testGenerate(Schema $schema, array $expected, SchemaRegistry $registry): void
    {
        self::assertEquals($expected, self::makeChoice($registry)->generate($schema));
    }
}
