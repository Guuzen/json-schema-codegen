<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\AddComment;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\DefaultCommentGenerator;
use Guuzen\JsonSchemaCodegen\Nette\AddComment\Comment;
use Guuzen\JsonSchemaCodegen\Nette\AddComment\NetteCommentFactory;
use Guuzen\JsonSchemaCodegen\Nette\AddComment\NoComment;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Nette\PhpGenerator\Parameter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CommentGeneratorTest extends TestCase
{
    /**
     * @return iterable<string, array{Schema, AddComment<Parameter>}>
     */
    public static function provideComments(): iterable
    {
        yield 'no description'       => [
            new Schema(type: SchemaType::String),
            new NoComment(),
        ];
        yield 'plain description'    => [
            new Schema(type: SchemaType::String, description: 'parent'),
            new Comment('parent'),
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
            new Comment("parent\n\nfirst branch\nthird branch"),
        ];
        yield 'anyOf branch descriptions without parent description' => [
            new Schema(
                anyOf: [
                    new Schema(type: SchemaType::String, description: 'first branch'),
                    new Schema(type: SchemaType::Integer, description: 'second branch'),
                ],
            ),
            new Comment("first branch\nsecond branch"),
        ];
        yield 'anyOf without any descriptions' => [
            new Schema(
                anyOf: [
                    new Schema(type: SchemaType::String),
                    new Schema(type: SchemaType::Null),
                ],
            ),
            new NoComment(),
        ];
    }

    /**
     * @param AddComment<Parameter> $expected
     */
    #[DataProvider('provideComments')]
    public function testCommentGeneration(Schema $schema, AddComment $expected): void
    {
        self::assertEquals($expected, new DefaultCommentGenerator(new NetteCommentFactory())->generate($schema));
    }
}
