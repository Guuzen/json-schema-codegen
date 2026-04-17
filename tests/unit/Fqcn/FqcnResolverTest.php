<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Fqcn;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Guuzen\JsonSchemaCodegen\Fqcn\Fqcn;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FqcnResolverTest extends TestCase
{
    public function testFromUriResolvesThroughConfiguredBaseUri(): void
    {
        $resolver = new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json');

        self::assertEquals(
            new Fqcn('App\\Dto\\Billing\\Address'),
            $resolver->fromUri(new AbsoluteUri('file:///schemas/Billing/Address.json')),
        );
    }

    /**
     * @return iterable<string, array{uri: string, baseUri: string, baseNamespace: string, suffix: non-empty-string, expected: string}>
     */
    public static function toFqcnProvider(): iterable
    {
        yield 'top-level URI produces simple FQCN' => [
            'uri' => 'file:///schemas/Person.json',
            'baseUri' => 'file:///schemas/',
            'baseNamespace' => 'App',
            'suffix' => '.json',
            'expected' => 'App\Person',
        ];

        yield 'nested URI produces namespaced FQCN' => [
            'uri' => 'file:///schemas/Models/Person.json',
            'baseUri' => 'file:///schemas/',
            'baseNamespace' => 'App',
            'suffix' => '.json',
            'expected' => 'App\Models\Person',
        ];

        yield 'deeply nested URI produces multiple namespace segments' => [
            'uri' => 'file:///schemas/Models/Dto/Person.json',
            'baseUri' => 'file:///schemas/',
            'baseNamespace' => 'App',
            'suffix' => '.json',
            'expected' => 'App\Models\Dto\Person',
        ];

        yield 'non-json extension is stripped' => [
            'uri' => 'file:///schemas/Person.yml',
            'baseUri' => 'file:///schemas/',
            'baseNamespace' => 'App',
            'suffix' => '.yml',
            'expected' => 'App\Person',
        ];

        yield 'case of URI segments is preserved exactly in the FQCN' => [
            'uri' => 'file:///schemas/myModel.json',
            'baseUri' => 'file:///schemas/',
            'baseNamespace' => 'App',
            'suffix' => '.json',
            'expected' => 'App\myModel',
        ];

        yield 'double extension' => [
            'uri' => 'file:///schemas/myModel.schema.json',
            'baseUri' => 'file:///schemas/',
            'baseNamespace' => 'App',
            'suffix' => '.schema.json',
            'expected' => 'App\myModel',
        ];

        yield 'base URI without trailing slash' => [
            'uri' => 'file:///schemas/Person.json',
            'baseUri' => 'file:///schemas',
            'baseNamespace' => 'App',
            'suffix' => '.json',
            'expected' => 'App\Person',
        ];

        yield 'suffix is only stripped from end of relative URI' => [
            'uri' => 'file:///schemas/ModelStore/PersonModel',
            'baseUri' => 'file:///schemas/',
            'baseNamespace' => 'App',
            'suffix' => 'Model',
            'expected' => 'App\ModelStore\Person',
        ];
    }

    /**
     * @param non-empty-string $suffix
     */
    #[DataProvider('toFqcnProvider')]
    public function testFromUri(
        string $uri,
        string $baseUri,
        string $baseNamespace,
        string $suffix,
        string $expected,
    ): void {
        $resolver = new FqcnResolver(new AbsoluteUri($baseUri), $baseNamespace, $suffix);

        self::assertEquals(new Fqcn($expected), $resolver->fromUri(new AbsoluteUri($uri)));
    }

    /**
     * @return iterable<string, array{uri: string}>
     */
    public static function uriWithNonPathComponentsProvider(): iterable
    {
        yield 'query' => ['uri' => 'file:///schemas/Person.json?version=1'];
        yield 'fragment' => ['uri' => 'file:///schemas/Person.json#/$defs/User'];
    }

    #[DataProvider('uriWithNonPathComponentsProvider')]
    public function testFromUriThrowsForUriWithQueryOrFragment(string $uri): void
    {
        $resolver = new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json');

        $this->expectException(\InvalidArgumentException::class);

        $resolver->fromUri(new AbsoluteUri($uri));
    }
}
