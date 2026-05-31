<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultDefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultValue as PropertyDefaultValue;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\NewObjectDefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\DefaultValue;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\Ref;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DefaultGeneratorTest extends TestCase
{
    private static function undefinedUri(): AbsoluteUri
    {
        return new AbsoluteUri('file:///schemas/Undefined.json');
    }

    private static function makeDefaultGenerator(): DefaultDefaultGenerator
    {
        return new DefaultDefaultGenerator(
            new FqcnResolver(new AbsoluteUri('file:///schemas/'), 'App\\Dto', '.json'),
            self::undefinedUri(),
        );
    }

    /**
     * @return iterable<string, array{Schema, ?PropertyDefaultValue}>
     */
    public static function provideDefaults(): iterable
    {
        yield 'string with default value' => [
            new Schema(type: SchemaType::String, required: false, default: new DefaultValue('foo')),
            new PropertyDefaultValue('foo'),
        ];
        yield 'int with default value' => [
            new Schema(type: SchemaType::Integer, required: false, default: new DefaultValue(1)),
            new PropertyDefaultValue(1),
        ];
        yield 'float with default value' => [
            new Schema(type: SchemaType::Number, required: false, default: new DefaultValue(1.1)),
            new PropertyDefaultValue(1.1),
        ];
        yield 'bool with default value' => [
            new Schema(type: SchemaType::Boolean, required: false, default: new DefaultValue(false)),
            new PropertyDefaultValue(false),
        ];
        yield 'null type with default value' => [
            new Schema(type: SchemaType::Null, required: false, default: new DefaultValue(null)),
            new PropertyDefaultValue(null),
        ];
        yield 'array with default value' => [
            new Schema(type: SchemaType::Array, required: false, default: new DefaultValue([])),
            new PropertyDefaultValue([]),
        ];
        yield 'enum strings with default value' => [
            new Schema(required: false, enum: ['active', 'disabled'], default: new DefaultValue('active')),
            new PropertyDefaultValue('active'),
        ];
        yield 'schema with no type with default string value' => [
            new Schema(required: false, default: new DefaultValue('some')),
            new PropertyDefaultValue('some'),
        ];
        yield 'ref schema with null default' => [
            new Schema(required: false, ref: new Ref(new AbsoluteUri('file:///schemas/Address.json')), default: new DefaultValue(null)),
            new PropertyDefaultValue(null),
        ];
        yield 'ref schema with object literal default' => [
            new Schema(
                required: false,
                ref: new Ref(new AbsoluteUri('file:///schemas/Address.json')),
                default: new DefaultValue(['street' => 'Main St', 'city' => 'Anytown']),
            ),
            new PropertyDefaultValue(new NewObjectDefaultValue('Address', ['street' => 'Main St', 'city' => 'Anytown'])),
        ];
        yield 'no default' => [new Schema(type: SchemaType::String, required: false), null];
        // No reason to specify undefined type without intention it to be Undefined type
        yield 'anyOf with undefined - default is Undefined' => [
            new Schema(
                required: false,
                anyOf: [
                    new Schema(type: null),
                    new Schema(ref: new Ref(self::undefinedUri())),
                ],
            ),
            new PropertyDefaultValue(new NewObjectDefaultValue('Undefined', [])),
        ];
        // A required property must not get a default value, even if the schema declares one -
        // otherwise it becomes an optional parameter ordered before required ones (invalid PHP).
        yield 'required property gets no default' => [
            new Schema(type: SchemaType::String, required: true, default: new DefaultValue('active')),
            null,
        ];
    }

    #[DataProvider('provideDefaults')]
    public function testDefault(Schema $schema, ?PropertyDefaultValue $expected): void
    {
        self::assertEquals($expected, self::makeDefaultGenerator()->generate($schema));
    }
}
