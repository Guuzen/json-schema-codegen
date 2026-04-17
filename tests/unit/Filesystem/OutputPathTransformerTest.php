<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Filesystem;

use Guuzen\JsonSchemaCodegen\Filesystem\OutputPathTransformer;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixPath;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OutputPathTransformerTest extends TestCase
{
    /**
     * @return iterable<string, array{schemaPath: string, outputPath: string, path: string, suffix: non-empty-string, expected: string}>
     */
    public static function generateProvider(): iterable
    {
        yield 'top-level output under base with trailing slash' => [
            'schemaPath' => '/schemas/',
            'outputPath' => '/generated/',
            'path' => '/schemas/Person.json',
            'suffix' => '.json',
            'expected' => '/generated/Person.php',
        ];

        yield 'top-level output under base without trailing slash' => [
            'schemaPath' => '/schemas/',
            'outputPath' => '/generated',
            'path' => '/schemas/Person.json',
            'suffix' => '.json',
            'expected' => '/generated/Person.php',
        ];

        yield 'nested output preserves relative path' => [
            'schemaPath' => '/schemas/',
            'outputPath' => '/generated/',
            'path' => '/schemas/Models/Person.schema.json',
            'suffix' => '.schema.json',
            'expected' => '/generated/Models/Person.php',
        ];

        yield 'schema base without trailing slash' => [
            'schemaPath' => '/schemas',
            'outputPath' => '/generated/',
            'path' => '/schemas/Person.json',
            'suffix' => '.json',
            'expected' => '/generated/Person.php',
        ];
    }

    /**
     * @param non-empty-string $suffix
     */
    #[DataProvider('generateProvider')]
    public function testGenerate(string $schemaPath, string $outputPath, string $path, string $suffix, string $expected): void
    {
        $transformer = new OutputPathTransformer(
            new AbsoluteUnixDirectoryPath($schemaPath),
            new AbsoluteUnixDirectoryPath($outputPath),
            $suffix,
        );

        self::assertEquals(new AbsoluteUnixPath($expected), $transformer->generate(new AbsoluteUnixPath($path)));
    }

    /**
     * @return iterable<string, array{schemaPath: string, path: string}>
     */
    public static function outsideSchemaBaseProvider(): iterable
    {
        yield 'completely different directory' => [
            'schemaPath' => '/schemas/',
            'path' => '/other/Person.json',
        ];

        yield 'same string prefix but different directory' => [
            'schemaPath' => '/schemas',
            'path' => '/schemas-extra/Person.json',
        ];
    }

    #[DataProvider('outsideSchemaBaseProvider')]
    public function testGenerateThrowsWhenPathIsOutsideSchemaBase(string $schemaPath, string $path): void
    {
        $transformer = new OutputPathTransformer(
            new AbsoluteUnixDirectoryPath($schemaPath),
            new AbsoluteUnixDirectoryPath('/generated/'),
            '.json',
        );

        $this->expectException(\InvalidArgumentException::class);

        $transformer->generate(new AbsoluteUnixPath($path));
    }

}
