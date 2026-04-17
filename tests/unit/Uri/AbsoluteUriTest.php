<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Uri;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AbsoluteUriTest extends TestCase
{
    /**
     * AbsoluteUri must reject strings that are not absolute URIs.
     * A scheme-only string like "file:" is also rejected because it does not
     * identify anything.
     *
     * @return iterable<string, array{value: string}>
     */
    public static function invalidUriProvider(): iterable
    {
        yield 'absolute path without scheme' => ['value' => '/schemas/Person.json'];
        yield 'relative path' => ['value' => 'schemas/Person.json'];
        yield 'scheme only, no authority or path' => ['value' => 'file:'];
        yield 'empty string' => ['value' => ''];
    }

    #[DataProvider('invalidUriProvider')]
    public function testConstructorThrowsForInvalidUri(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AbsoluteUri($value);
    }

}
