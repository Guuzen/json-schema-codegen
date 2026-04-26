<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
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
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Undefined;

/**
 * @implements PropertyGenerator<ResolvedAnnotation, array{type: PhpType}>
 */
final readonly class AnnotationGenerator implements PropertyGenerator
{
    public static function default(): self
    {
        return new self();
    }

    public function generate(Schema $schema, mixed $params): ResolvedAnnotation
    {
        return $this->render($params['type']);
    }

    private function render(PhpType $type): ResolvedAnnotation
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
        $rendered = $this->render($type->itemType);
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
            $rendered = $this->render($branch);
            $annotations[] = $rendered->annotation;
            $imports = [...$imports, ...$rendered->imports];
        }

        return new ResolvedAnnotation(implode('|', $annotations), $imports);
    }
}
