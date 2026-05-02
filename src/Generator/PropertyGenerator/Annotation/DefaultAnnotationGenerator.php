<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

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

final readonly class DefaultAnnotationGenerator implements AnnotationGenerator
{
    public function generate(PhpType $type): ResolvedAnnotation
    {
        return match (true) {
            $type instanceof StringType => new ResolvedAnnotation($type->nonEmpty ? 'non-empty-string' : 'string', []),
            $type instanceof IntType => new ResolvedAnnotation($this->renderInt($type), []),
            $type instanceof FloatType => new ResolvedAnnotation('float', []),
            $type instanceof BoolType => new ResolvedAnnotation('bool', []),
            $type instanceof NullType => new ResolvedAnnotation('null', []),
            $type instanceof ObjectType => new ResolvedAnnotation('object', []),
            $type instanceof MixedType => new ResolvedAnnotation('mixed', []),
            $type instanceof ListType => $this->renderList($type),
            $type instanceof ClassRefType => new ResolvedAnnotation($type->alias, [['alias' => $type->alias, 'fqcn' => $type->fqcn]]),
            $type instanceof EnumLiteralType => new ResolvedAnnotation($this->renderEnum($type), []),
            $type instanceof UnionType => $this->renderUnion($type),
            $type instanceof UndefinedType => new ResolvedAnnotation('Undefined', [['alias' => 'Undefined', 'fqcn' => Undefined::class]]),
            default => new ResolvedAnnotation('mixed', []),
        };
    }

    private function renderInt(IntType $type): string
    {
        if ($type->min === null && $type->max === null) {
            return 'int';
        }

        $min = $type->min !== null ? (string)$type->min : 'min';
        $max = $type->max !== null ? (string)$type->max : 'max';

        return "int<{$min}, {$max}>";
    }

    private function renderList(ListType $type): ResolvedAnnotation
    {
        $rendered = $this->generate($type->itemType);
        $prefix = $type->nonEmpty ? 'non-empty-list' : 'list';

        return new ResolvedAnnotation("{$prefix}<{$rendered->annotation}>", $rendered->imports);
    }

    private function renderEnum(EnumLiteralType $type): string
    {
        return implode(
            '|', array_map(
            fn(string|int $v) => is_string($v) ? "'{$v}'" : (string)$v,
            $type->values,
        )
        );
    }

    private function renderUnion(UnionType $type): ResolvedAnnotation
    {
        $annotations = [];
        $imports = [];

        foreach ($type->types as $branch) {
            $rendered = $this->generate($branch);
            $annotations[] = $rendered->annotation;
            $imports = [...$imports, ...$rendered->imports];
        }

        return new ResolvedAnnotation(implode('|', $annotations), $imports);
    }
}
