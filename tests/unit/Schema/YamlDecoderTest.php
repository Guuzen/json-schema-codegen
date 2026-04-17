<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Schema;

use Guuzen\JsonSchemaCodegen\Schema\YamlDecoder;
use PHPUnit\Framework\TestCase;

final class YamlDecoderTest extends TestCase
{
    /** Decodes valid YAML content into an array with string keys */
    public function testDecodesValidYaml(): void
    {
        $decoder = new YamlDecoder();

        $result = $decoder->decode("type: object\ntitle: Foo\n");

        self::assertSame(['type' => 'object', 'title' => 'Foo'], $result);
    }

    /** Throws when content is not valid YAML */
    public function testThrowsOnInvalidYaml(): void
    {
        $decoder = new YamlDecoder();

        $this->expectException(\InvalidArgumentException::class);

        $decoder->decode("foo: bar: baz: :\n");
    }

    /** Throws when YAML root is not a mapping (e.g. a plain string or list) */
    public function testThrowsWhenRootIsNotAMapping(): void
    {
        $decoder = new YamlDecoder();

        $this->expectException(\InvalidArgumentException::class);

        $decoder->decode("- item1\n- item2\n");
    }
}
