<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\BoolType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\ClassRefType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\FloatType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\MixedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\NullType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\ObjectType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\UndefinedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\Undefined;

final readonly class DefaultTypeGenerator implements TypeGenerator
{
    public function generate(PhpType $type): array
    {
        return match (true) {
            $type instanceof StringType      => [new ResolvedType('string')],
            $type instanceof IntType         => [new ResolvedType('integer')],
            $type instanceof FloatType       => [new ResolvedType('float')],
            $type instanceof BoolType        => [new ResolvedType('bool')],
            $type instanceof NullType        => [],
            $type instanceof ObjectType      => [new ResolvedType('object')],
            $type instanceof MixedType       => [],
            $type instanceof ListType        => [new ResolvedType('list')],
            $type instanceof ClassRefType    => [new ResolvedType($type->alias, ['alias' => $type->alias, 'fqcn' => $type->fqcn])],
            $type instanceof EnumLiteralType => $this->renderEnum($type),
            $type instanceof UnionType       => $this->renderUnion($type),
            $type instanceof UndefinedType   => [new ResolvedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class])],
            default                          => [],
        };
    }

    /**
     * @return list<ResolvedType>
     */
    private function renderEnum(EnumLiteralType $type): array
    {
        $types = [];

        if (array_filter($type->values, 'is_string') !== []) {
            $types[] = 'string';
        }
        if (array_filter($type->values, 'is_int') !== []) {
            $types[] = 'integer';
        }

        return array_map(fn(string $t) => new ResolvedType($t), $types);
    }

    /**
     * @return list<ResolvedType>
     */
    private function renderUnion(UnionType $type): array
    {
        $results = [];

        foreach ($type->types as $branch) {
            $rendered = $this->generate($branch);
            array_push($results, ...$rendered);
        }

        return $results;
    }
}
