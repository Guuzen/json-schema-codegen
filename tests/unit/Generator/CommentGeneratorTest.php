<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\CommentGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use PHPUnit\Framework\TestCase;

final class CommentGeneratorTest extends TestCase
{
    public function testCommentIsNullWhenSchemaHasNoDescription(): void
    {
        self::assertNull(new CommentGenerator()->generate(new Schema(type: SchemaType::String)));
    }
}
