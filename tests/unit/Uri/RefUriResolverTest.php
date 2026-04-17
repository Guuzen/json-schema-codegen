<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Uri;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Guuzen\JsonSchemaCodegen\Uri\RefUriResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RefUriResolverTest extends TestCase
{
    /**
     * @return iterable<string, array{ref: string, schemaUri: string, expected: string}>
     */
    public static function fromRefProvider(): iterable
    {
        yield 'absolute ref is returned as-is' => [
            'ref' => 'https://example.com/other.json',
            'schemaUri' => 'file:///schemas/Foo.json',
            'expected' => 'https://example.com/other.json',
        ];

        yield 'relative ref resolved against schema URI directory' => [
            'ref' => 'Bar.json',
            'schemaUri' => 'file:///schemas/Foo.json',
            'expected' => 'file:///schemas/Bar.json',
        ];

        yield 'ref with .. moves up one directory level' => [
            'ref' => '../other/Bar.json',
            'schemaUri' => 'file:///schemas/sub/Foo.json',
            'expected' => 'file:///schemas/other/Bar.json',
        ];

        yield 'ref with . keeps current directory' => [
            'ref' => './Bar.json',
            'schemaUri' => 'file:///schemas/Foo.json',
            'expected' => 'file:///schemas/Bar.json',
        ];

        yield 'ref with multiple .. traverses multiple levels up' => [
            'ref' => '../../Bar.json',
            'schemaUri' => 'file:///schemas/a/b/Foo.json',
            'expected' => 'file:///schemas/Bar.json',
        ];

        yield 'ref with subdirectory resolves into that subdirectory' => [
            'ref' => 'sub/Bar.json',
            'schemaUri' => 'file:///schemas/Foo.json',
            'expected' => 'file:///schemas/sub/Bar.json',
        ];

        yield 'ref with ./ followed by ../ resolves correctly' => [
            'ref' => './../Foo/Bar.json',
            'schemaUri' => 'file:///schemas/Order/Detail/Item.json',
            'expected' => 'file:///schemas/Order/Foo/Bar.json',
        ];

        yield 'ref path is normalized by URI resolver' => [
            'ref' => 'Foo/../Bar.json',
            'schemaUri' => 'file:///schemas/Order.json',
            'expected' => 'file:///schemas/Bar.json',
        ];
    }

    #[DataProvider('fromRefProvider')]
    public function testFromRef(string $ref, string $schemaUri, string $expected): void
    {
        $result = new RefUriResolver()->resolve($ref, new AbsoluteUri($schemaUri));

        self::assertEquals(new AbsoluteUri($expected), $result);
    }

    /**
     * @return iterable<string, array{ref: string, schemaUri: string}>
     */
    public static function unsupportedFragmentRefProvider(): iterable
    {
        yield 'local JSON Pointer fragment' => [
            'ref' => '#/$defs/User',
            'schemaUri' => 'file:///schemas/Foo.json',
        ];

        yield 'relative file ref with JSON Pointer fragment' => [
            'ref' => 'Bar.json#/$defs/User',
            'schemaUri' => 'file:///schemas/Foo.json',
        ];

        yield 'absolute file ref with JSON Pointer fragment' => [
            'ref' => 'file:///schemas/Bar.json#/$defs/User',
            'schemaUri' => 'file:///schemas/Foo.json',
        ];

        yield 'empty fragment' => [
            'ref' => 'Bar.json#',
            'schemaUri' => 'file:///schemas/Foo.json',
        ];
    }

    #[DataProvider('unsupportedFragmentRefProvider')]
    public function testFromRefThrowsForUnsupportedFragments(string $ref, string $schemaUri): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON Schema ref fragments are not supported');

        new RefUriResolver()->resolve($ref, new AbsoluteUri($schemaUri));
    }
}
