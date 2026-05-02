<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Tests\Unit\Generator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\DefaultAnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\ResolvedAnnotation;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\BoolType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ClassRefType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\FloatType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\MixedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\NullType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ObjectType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UndefinedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\Undefined;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AnnotationGeneratorTest extends TestCase
{
    /**
     * @return iterable<string, array{PhpType, ResolvedAnnotation}>
     */
    public static function provideAnnotations(): iterable
    {
        // Primitives
        yield 'string'           => [new StringType(), new ResolvedAnnotation('string', [])];
        yield 'non-empty-string' => [new StringType(nonEmpty: true), new ResolvedAnnotation('non-empty-string', [])];
        yield 'int'              => [new IntType(), new ResolvedAnnotation('int', [])];
        yield 'int min'          => [new IntType(min: 5), new ResolvedAnnotation('int<5, max>', [])];
        yield 'int max'          => [new IntType(max: 10), new ResolvedAnnotation('int<min, 10>', [])];
        yield 'int bounded'      => [new IntType(min: 1, max: 100), new ResolvedAnnotation('int<1, 100>', [])];
        yield 'int zero min'     => [new IntType(min: 0), new ResolvedAnnotation('int<0, max>', [])];
        yield 'int negative'     => [
            new IntType(min: -100, max: -1),
            new ResolvedAnnotation('int<-100, -1>', []),
        ];
        yield 'float'  => [new FloatType(), new ResolvedAnnotation('float', [])];
        yield 'bool'   => [new BoolType(), new ResolvedAnnotation('bool', [])];
        yield 'null'   => [new NullType(), new ResolvedAnnotation('null', [])];
        yield 'object' => [new ObjectType(), new ResolvedAnnotation('object', [])];
        yield 'mixed'  => [new MixedType(), new ResolvedAnnotation('mixed', [])];
        yield 'undefined' => [
            new UndefinedType(),
            new ResolvedAnnotation('Undefined', [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
        ];

        // Lists
        yield 'list of mixed' => [
            new ListType(new MixedType()),
            new ResolvedAnnotation('list<mixed>', []),
        ];
        yield 'non-empty-list of mixed' => [
            new ListType(new MixedType(), nonEmpty: true),
            new ResolvedAnnotation('non-empty-list<mixed>', []),
        ];
        yield 'list of string' => [
            new ListType(new StringType()),
            new ResolvedAnnotation('list<string>', []),
        ];
        yield 'list of int' => [
            new ListType(new IntType()),
            new ResolvedAnnotation('list<int>', []),
        ];
        yield 'list of object' => [
            new ListType(new ObjectType()),
            new ResolvedAnnotation('list<object>', []),
        ];
        yield 'non-empty-list of string' => [
            new ListType(new StringType(), nonEmpty: true),
            new ResolvedAnnotation('non-empty-list<string>', []),
        ];
        yield 'list of class ref' => [
            new ListType(new ClassRefType('Tag', 'App\\Dto\\Tag')),
            new ResolvedAnnotation('list<Tag>', [['alias' => 'Tag', 'fqcn' => 'App\\Dto\\Tag']]),
        ];
        yield 'non-empty-list of class ref'      => [
            new ListType(new ClassRefType('Tag', 'App\\Dto\\Tag'), nonEmpty: true),
            new ResolvedAnnotation('non-empty-list<Tag>', [['alias' => 'Tag', 'fqcn' => 'App\\Dto\\Tag']]),
        ];

        // Class refs
        yield 'class ref'                  => [
            new ClassRefType('Address', 'App\\Dto\\Address'),
            new ResolvedAnnotation('Address', [['alias' => 'Address', 'fqcn' => 'App\\Dto\\Address']]),
        ];
        yield 'class ref with alias'       => [
            new ClassRefType('BillingAddress', 'App\\Dto\\Address'),
            new ResolvedAnnotation('BillingAddress', [['alias' => 'BillingAddress', 'fqcn' => 'App\\Dto\\Address']]),
        ];

        // Enums
        yield 'enum of strings'   => [new EnumLiteralType(['foo', 'bar']), new ResolvedAnnotation("'foo'|'bar'", [])];
        yield 'enum of ints'      => [new EnumLiteralType([1, 2]), new ResolvedAnnotation('1|2', [])];
        yield 'enum of mixed'     => [new EnumLiteralType(['foo', 1]), new ResolvedAnnotation("'foo'|1", [])];
        yield 'enum single value' => [new EnumLiteralType(['pending']), new ResolvedAnnotation("'pending'", [])];
        yield 'enum three ints'   => [new EnumLiteralType([1, 2, 3]), new ResolvedAnnotation('1|2|3', [])];

        // Unions
        yield 'union string null'                => [
            new UnionType([new StringType(), new NullType()]),
            new ResolvedAnnotation('string|null', []),
        ];
        yield 'union string int'                 => [
            new UnionType([new StringType(), new IntType()]),
            new ResolvedAnnotation('string|int', []),
        ];
        yield 'union nullable non-empty-string'  => [
            new UnionType([new StringType(nonEmpty: true), new NullType()]),
            new ResolvedAnnotation('non-empty-string|null', []),
        ];
        yield 'union string bool'                => [
            new UnionType([new StringType(), new BoolType()]),
            new ResolvedAnnotation('string|bool', []),
        ];
        yield 'union nullable non-empty list'    => [
            new UnionType([new ListType(new StringType(), nonEmpty: true), new NullType()]),
            new ResolvedAnnotation('non-empty-list<string>|null', []),
        ];
        yield 'union ref and null'               => [
            new UnionType([new ClassRefType('Person', 'App\\Dto\\Person'), new NullType()]),
            new ResolvedAnnotation('Person|null', [['alias' => 'Person', 'fqcn' => 'App\\Dto\\Person']]),
        ];
        yield 'union two refs'                   => [
            new UnionType([
                new ClassRefType('Cat', 'App\\Dto\\Cat'),
                new ClassRefType('Dog', 'App\\Dto\\Dog'),
            ]),
            new ResolvedAnnotation('Cat|Dog', [
                ['alias' => 'Cat', 'fqcn' => 'App\\Dto\\Cat'],
                ['alias' => 'Dog', 'fqcn' => 'App\\Dto\\Dog'],
            ]),
        ];
        yield 'union three refs'                 => [
            new UnionType([
                new ClassRefType('Cat', 'App\\Dto\\Cat'),
                new ClassRefType('Dog', 'App\\Dto\\Dog'),
                new ClassRefType('Bird', 'App\\Dto\\Bird'),
            ]),
            new ResolvedAnnotation('Cat|Dog|Bird', [
                ['alias' => 'Cat', 'fqcn' => 'App\\Dto\\Cat'],
                ['alias' => 'Dog', 'fqcn' => 'App\\Dto\\Dog'],
                ['alias' => 'Bird', 'fqcn' => 'App\\Dto\\Bird'],
            ]),
        ];
        yield 'union string undefined (optional)' => [
            new UnionType([new StringType(), new UndefinedType()]),
            new ResolvedAnnotation('string|Undefined', [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
        ];
    }

    #[DataProvider('provideAnnotations')]
    public function testAnnotation(PhpType $type, ResolvedAnnotation $expected): void
    {
        self::assertEquals($expected, new DefaultAnnotationGenerator()->generate($type));
    }
}
