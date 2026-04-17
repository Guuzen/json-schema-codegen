<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Path;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;
use Guuzen\JsonSchemaCodegen\Path\RelativeUnixPath;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\TestCase;

final class AbsoluteUnixDirectoryPathTest extends TestCase
{
    public function testConstructorNormalizesTrailingSlash(): void
    {
        self::assertSame('/schemas/', new AbsoluteUnixDirectoryPath('/schemas')->value);
    }

    public function testConstructorRejectsRelativePath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AbsoluteUnixDirectoryPath('relative/path');
    }

    public function testResolveReturnsAbsolutePath(): void
    {
        self::assertEquals(
            new AbsoluteUnixPath('/schemas/Models/Person.json'),
            new AbsoluteUnixDirectoryPath('/schemas')->resolve(new RelativeUnixPath('Models/Person.json')),
        );
    }

    public function testToUriReturnsDirectoryFileUri(): void
    {
        self::assertEquals(
            new AbsoluteUri('file:///tmp/schemas/'),
            new AbsoluteUnixDirectoryPath('/tmp/schemas')->toUri(),
        );
    }
}
