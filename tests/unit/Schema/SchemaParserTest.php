<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Schema;

use Guuzen\JsonSchemaCodegen\Schema\SchemaParser;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use PHPUnit\Framework\TestCase;

final class SchemaParserTest extends TestCase
{
    public function testMarksPropertiesListedInRequiredKeyword(): void
    {
        $schema = new SchemaParser()->parse(
            [
                'type' => 'object',
                'required' => ['id'],
                'properties' => [
                    'id' => ['type' => 'string'],
                    'note' => ['type' => 'string'],
                ],
            ],
            new AbsoluteUri('file:///schemas/Order.json'),
        );

        self::assertSame(
            ['id' => true, 'note' => false],
            array_map(
                static fn($propertySchema): bool => $propertySchema->required,
                $schema->properties ?? [],
            ),
        );
    }
}
