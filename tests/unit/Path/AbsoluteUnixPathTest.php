<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Path;

use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\RelativeUnixPath;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\TestCase;

final class AbsoluteUnixPathTest extends TestCase
{
    public function testConstructorAllowsAbsoluteUnixPath(): void
    {
        self::assertSame('/schemas/Person.json', new AbsoluteUnixPath('/schemas/Person.json')->value);
    }

    public function testConstructorRejectsRelativePath(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AbsoluteUnixPath('schemas/Person.json');
    }

    public function testRelativeToReturnsRelativePath(): void
    {
        self::assertEquals(
            new RelativeUnixPath('Models/Person.json'),
            new AbsoluteUnixPath('/schemas/Models/Person.json')->relativeTo(
                new AbsoluteUnixDirectoryPath('/schemas'),
            ),
        );
    }

    public function testRelativeToThrowsWhenPathIsOutsideBase(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AbsoluteUnixPath('/other/Person.json')->relativeTo(
            new AbsoluteUnixDirectoryPath('/schemas'),
        );
    }

    public function testToUriReturnsFileUri(): void
    {
        self::assertEquals(
            new AbsoluteUri('file:///tmp/My%20Schema.json'),
            new AbsoluteUnixPath('/tmp/My Schema.json')->toUri(),
        );
    }
}
