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
    public function generate(PhpType $type): ResolvedTypes
    {
        return match (true) {
            $type instanceof StringType      => new ResolvedTypes(['string'], []),
            $type instanceof IntType         => new ResolvedTypes(['integer'], []),
            $type instanceof FloatType       => new ResolvedTypes(['float'], []),
            $type instanceof BoolType        => new ResolvedTypes(['bool'], []),
            $type instanceof NullType        => new ResolvedTypes([], []),
            $type instanceof ObjectType      => new ResolvedTypes(['object'], []),
            $type instanceof MixedType       => new ResolvedTypes([], []),
            $type instanceof ListType        => new ResolvedTypes(['list'], []),
            $type instanceof ClassRefType    => new ResolvedTypes([$type->alias], [['alias' => $type->alias, 'fqcn' => $type->fqcn]]),
            $type instanceof EnumLiteralType => $this->renderEnum($type),
            $type instanceof UnionType       => $this->renderUnion($type),
            $type instanceof UndefinedType   => new ResolvedTypes(['Undefined'], [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
            default                          => new ResolvedTypes([], []),
        };
    }

    private function renderEnum(EnumLiteralType $type): ResolvedTypes
    {
        $types = [];

        if (array_filter($type->values, 'is_string') !== []) {
            $types[] = 'string';
        }
        if (array_filter($type->values, 'is_int') !== []) {
            $types[] = 'integer';
        }

        return new ResolvedTypes($types, []);
    }

    private function renderUnion(UnionType $type): ResolvedTypes
    {
        $typesByKey   = [];
        $importsByKey = [];

        foreach ($type->types as $branch) {
            $rendered = $this->generate($branch);

            foreach ($rendered->types as $t) {
                $typesByKey[$t] = $t;
            }
            foreach ($rendered->imports as $import) {
                $importsByKey[$import['alias'] . ':' . $import['fqcn']] = $import;
            }
        }

        return new ResolvedTypes(array_values($typesByKey), array_values($importsByKey));
    }
}
