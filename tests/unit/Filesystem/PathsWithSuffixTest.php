<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Filesystem;

use Guuzen\JsonSchemaCodegen\Filesystem\PathsWithSuffix;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;
use PHPUnit\Framework\TestCase;

final class PathsWithSuffixTest extends TestCase
{
    public function testCollectYieldsFilesWithMatchingSuffix(): void
    {
        $files = new \ArrayIterator([
            new \SplFileInfo('/schemas/Person.json'),
        ]);
        $collector = new PathsWithSuffix($files, '.json');

        $result = iterator_to_array($collector->collect());

        self::assertEquals(
            [new AbsoluteUnixPath('/schemas/Person.json')],
            $result,
        );
    }

    public function testCollectFiltersOutFilesWithDifferentSuffix(): void
    {
        $files = new \ArrayIterator([
            new \SplFileInfo('/schemas/Person.yaml'),
        ]);
        $collector = new PathsWithSuffix($files, '.json');

        $result = iterator_to_array($collector->collect());

        self::assertSame([], $result);
    }

    public function testCollectYieldsNothingForEmptyInput(): void
    {
        $collector = new PathsWithSuffix(new \ArrayIterator([]), '.json');

        $result = iterator_to_array($collector->collect());

        self::assertSame([], $result);
    }

    public function testCollectHandlesMixedExtensions(): void
    {
        $files = new \ArrayIterator([
            new \SplFileInfo('/schemas/Person.json'),
            new \SplFileInfo('/schemas/Person.yaml'),
            new \SplFileInfo('/schemas/Address.json'),
        ]);
        $collector = new PathsWithSuffix($files, '.json');

        $result = iterator_to_array($collector->collect());

        self::assertEquals(
            [
                new AbsoluteUnixPath('/schemas/Person.json'),
                new AbsoluteUnixPath('/schemas/Address.json'),
            ],
            $result,
        );
    }

    public function testCollectWithYamlSuffix(): void
    {
        $files = new \ArrayIterator([
            new \SplFileInfo('/schemas/Person.json'),
            new \SplFileInfo('/schemas/Order.yaml'),
            new \SplFileInfo('/schemas/Address.yaml'),
        ]);
        $collector = new PathsWithSuffix($files, '.yaml');

        $result = iterator_to_array($collector->collect());

        self::assertEquals(
            [
                new AbsoluteUnixPath('/schemas/Order.yaml'),
                new AbsoluteUnixPath('/schemas/Address.yaml'),
            ],
            $result,
        );
    }

    /**
     * To distinguish between files with same extensions we can use different suffixes
     */
    public function testCollectWithDoubleExtensionSuffix(): void
    {
        $files = new \ArrayIterator([
            new \SplFileInfo('/schemas/Person.json'),
            new \SplFileInfo('/schemas/Order.schema.json'),
            new \SplFileInfo('/schemas/Address.schema.json'),
        ]);
        $collector = new PathsWithSuffix($files, '.schema.json');

        $result = iterator_to_array($collector->collect());

        self::assertEquals(
            [
                new AbsoluteUnixPath('/schemas/Order.schema.json'),
                new AbsoluteUnixPath('/schemas/Address.schema.json'),
            ],
            $result,
        );
    }

    public function testCreateCollectsPathsFromDirectory(): void
    {
        $collector = PathsWithSuffix::create(
            new AbsoluteUnixDirectoryPath(__DIR__),
            'PathsWithSuffixTest.php',
        );

        $result = iterator_to_array($collector->collect());

        self::assertContainsEquals(new AbsoluteUnixPath(__FILE__), $result);
    }

}
