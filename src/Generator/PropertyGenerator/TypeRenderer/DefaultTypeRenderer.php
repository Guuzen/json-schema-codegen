<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer;

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

final readonly class DefaultTypeRenderer implements TypeRenderer
{
    public function render(PhpType $type): array
    {
        return match (true) {
            $type instanceof StringType      => [new RenderedType('string')],
            $type instanceof IntType         => [new RenderedType('integer')],
            $type instanceof FloatType       => [new RenderedType('float')],
            $type instanceof BoolType        => [new RenderedType('bool')],
            $type instanceof NullType        => [],
            $type instanceof ObjectType      => [new RenderedType('object')],
            $type instanceof MixedType       => [],
            $type instanceof ListType        => [new RenderedType('list')],
            $type instanceof ClassRefType    => [new RenderedType($type->alias, ['alias' => $type->alias, 'fqcn' => $type->fqcn])],
            $type instanceof EnumLiteralType => $this->renderEnum($type),
            $type instanceof UnionType       => $this->renderUnion($type),
            $type instanceof UndefinedType   => [new RenderedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class])],
            default                          => [],
        };
    }

    /**
     * @return list<RenderedType>
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

        return array_map(fn(string $t) => new RenderedType($t), $types);
    }

    /**
     * @return list<RenderedType>
     */
    private function renderUnion(UnionType $type): array
    {
        $results = [];

        foreach ($type->types as $branch) {
            $rendered = $this->render($branch);
            array_push($results, ...$rendered);
        }

        return $results;
    }
}
