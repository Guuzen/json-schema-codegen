<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\DefaultCommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Generator\SchemaTreeBuilder;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CommentGeneratorTest extends TestCase
{
    /**
     * @return iterable<string, array{Schema, ?string}>
     */
    public static function provideComments(): iterable
    {
        yield 'no description'       => [
            new Schema(type: SchemaType::String),
            null,
        ];
        yield 'plain description'    => [
            new Schema(type: SchemaType::String, description: 'parent'),
            'parent',
        ];
        yield 'anyOf branch descriptions appended to parent description' => [
            new Schema(
                anyOf: [
                    new Schema(type: SchemaType::String, description: 'first branch'),
                    new Schema(type: SchemaType::Null),
                    new Schema(type: SchemaType::Integer, description: 'third branch'),
                ],
                description: 'parent',
            ),
            "parent\n\nfirst branch\nthird branch",
        ];
        yield 'anyOf branch descriptions without parent description' => [
            new Schema(
                anyOf: [
                    new Schema(type: SchemaType::String, description: 'first branch'),
                    new Schema(type: SchemaType::Integer, description: 'second branch'),
                ],
            ),
            "first branch\nsecond branch",
        ];
        yield 'anyOf without any descriptions' => [
            new Schema(
                anyOf: [
                    new Schema(type: SchemaType::String),
                    new Schema(type: SchemaType::Null),
                ],
            ),
            null,
        ];
    }

    #[DataProvider('provideComments')]
    public function testCommentGeneration(Schema $schema, ?string $expected): void
    {
        $tree = new SchemaTreeBuilder(new SchemaRegistry([]))->build($schema);

        self::assertSame($expected, new DefaultCommentGenerator()->generate($tree));
    }
}
