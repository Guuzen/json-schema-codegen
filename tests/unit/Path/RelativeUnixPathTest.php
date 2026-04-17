<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Path;

use Guuzen\JsonSchemaCodegen\Path\RelativeUnixPath;
use PHPUnit\Framework\TestCase;

final class RelativeUnixPathTest extends TestCase
{
    public function testAllowsRelativeUnixPath(): void
    {
        self::assertSame('schemas/Person.json', new RelativeUnixPath('schemas/Person.json')->value);
    }

    public function testRejectsAbsolutePath(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RelativeUnixPath('/schemas/Person.json');
    }
}
