<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Generator\ConstructorParameterOrder;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use PHPUnit\Framework\TestCase;

final class ConstructorParameterOrderTest extends TestCase
{
    public function testPlacesOptionalPropertiesAfterRequiredOnes(): void
    {
        $orderedProperties = new ConstructorParameterOrder()->order(
            new Schema(
                type: SchemaType::Object,
                properties: [
                    'optionalFirst' => new Schema(type: SchemaType::String, required: false),
                    'requiredMiddle' => new Schema(type: SchemaType::String, required: true),
                    'optionalLast' => new Schema(type: SchemaType::String, required: false),
                    'requiredLast' => new Schema(type: SchemaType::String, required: true),
                ],
            ),
        );

        self::assertSame(
            ['requiredMiddle', 'requiredLast', 'optionalFirst', 'optionalLast'],
            array_keys($orderedProperties),
        );
    }
}
