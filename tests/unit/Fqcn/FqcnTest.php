<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Fqcn;

use Guuzen\JsonSchemaCodegen\Fqcn\Fqcn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FqcnTest extends TestCase
{
    /**
     * @return iterable<string, array{fqcn: string, expected: string}>
     */
    public static function classNameProvider(): iterable
    {
        yield 'with namespace' => [
            'fqcn' => 'App\\Dto\\Person',
            'expected' => 'Person',
        ];

        yield 'without namespace' => [
            'fqcn' => 'Person',
            'expected' => 'Person',
        ];
    }

    #[DataProvider('classNameProvider')]
    public function testClassName(string $fqcn, string $expected): void
    {
        self::assertSame($expected, new Fqcn($fqcn)->className());
    }

    /**
     * @return iterable<string, array{fqcn: string, expected: string}>
     */
    public static function namespaceProvider(): iterable
    {
        yield 'with namespace' => [
            'fqcn' => 'App\\Dto\\Person',
            'expected' => 'App\\Dto',
        ];
    }

    #[DataProvider('namespaceProvider')]
    public function testNamespace(string $fqcn, string $expected): void
    {
        self::assertSame($expected, new Fqcn($fqcn)->namespace());
    }

    /**
     * @return iterable<string, array{fqcn: string}>
     */
    public static function invalidFqcnProvider(): iterable
    {
        yield 'empty' => [
            'fqcn' => '',
        ];

        yield 'no class name' => [
            'fqcn' => 'App\\Dto\\',
        ];
    }

    #[DataProvider('invalidFqcnProvider')]
    public function testConstructorThrowsForInvalidFqcn(string $fqcn): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Fqcn($fqcn);
    }
}
