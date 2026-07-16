<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Nette;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Nette\EnumClassGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\Printer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EnumClassGeneratorTest extends TestCase
{
    private static function makeGenerator(): EnumClassGenerator
    {
        return new EnumClassGenerator(
            new Printer(),
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
        );
    }

    /**
     * @return iterable<string, array{Schema}>
     */
    public static function provideNonEnums(): iterable
    {
        yield 'no enum keyword' => [new Schema()];
        yield 'enum with an integer' => [new Schema(enum: [1])];
        yield 'enum with a numeric string' => [new Schema(enum: ['1'])];
    }

    #[DataProvider('provideNonEnums')]
    public function testGeneratesNothing(Schema $schema): void
    {
        self::assertNull(
            self::makeGenerator()->generate(
                new AbsoluteUri('file:///schemas/OrderStatus.json'),
                $schema
            )
        );
    }
}
