<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\BoolType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ClassRefType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\EnumLiteralType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\FloatType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\MixedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\NullType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\ObjectType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UndefinedType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\PhpType\UnionType;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class TypeGenerator
{
    public function __construct(
        private FqcnResolver   $fqcnResolver,
        private SchemaRegistry $registry,
    ) {
    }

    public function generate(Schema $schema): PhpType
    {
        $type = $this->resolveType($schema);

        if (!$schema->required && $schema->default === null) {
            return $this->withUndefined($type);
        }

        return $type;
    }

    private function resolveType(Schema $schema): PhpType
    {
        if ($schema->ref !== null) {
            $referenced = $this->registry->get($schema->ref->uri);

            if ($referenced->type === SchemaType::Object) {
                $fqcn  = $this->fqcnResolver->fromUri($schema->ref->uri);
                $alias = $schema->title ?? $fqcn->className();
                return new ClassRefType($alias, $fqcn->fqcn);
            }

            return $this->resolveType($referenced);
        }

        if ($schema->oneOf !== null) {
            return $this->resolveUnion(
                array_map(fn(Schema $branch) => $this->resolveOneOfBranch($branch), $schema->oneOf),
            );
        }

        if (is_array($schema->type)) {
            return $this->resolveUnion(
                array_map(
                    fn(SchemaType $t) => $t === SchemaType::Null ? null : $this->resolveType(new Schema(type: $t)),
                    $schema->type,
                ),
            );
        }

        if ($schema->enum !== null) {
            return new EnumLiteralType($schema->enum);
        }

        return match ($schema->type) {
            SchemaType::String  => new StringType(
                nonEmpty: $schema->minLength !== null && $schema->minLength >= 1,
                format: $schema->format,
                pattern: $schema->pattern,
            ),
            SchemaType::Integer => new IntType(min: $schema->minimum, max: $schema->maximum),
            SchemaType::Number  => new FloatType(),
            SchemaType::Boolean => new BoolType(),
            SchemaType::Null    => new NullType(),
            SchemaType::Object  => new ObjectType(),
            SchemaType::Array   => $this->resolveArray($schema),
            default             => new MixedType(),
        };
    }

    private function resolveOneOfBranch(Schema $branch): ?PhpType
    {
        if ($branch->type === SchemaType::Null) {
            return null;
        }

        return $this->resolveType($branch);
    }

    /**
     * @param list<?PhpType> $branches  null entries represent the Null type and are moved to end
     */
    private function resolveUnion(array $branches): PhpType
    {
        $nonNull = array_values(array_filter($branches, fn(?PhpType $b) => $b !== null));
        $hasNull = count($nonNull) < count($branches);

        $types = $nonNull;
        if ($hasNull) {
            $types[] = new NullType();
        }

        return match (count($types)) {
            0       => new MixedType(),
            1       => $types[0],
            default => new UnionType($types),
        };
    }

    private function resolveArray(Schema $schema): PhpType
    {
        $itemType = $schema->items !== null ? $this->resolveType($schema->items) : new MixedType();
        $nonEmpty = $schema->minItems !== null && $schema->minItems >= 1;

        return new ListType($itemType, nonEmpty: $nonEmpty);
    }

    private function withUndefined(PhpType $type): PhpType
    {
        if ($type instanceof MixedType) {
            return $type;
        }

        $existing = $type instanceof UnionType ? $type->types : [$type];

        return new UnionType([...$existing, new UndefinedType()]);
    }
}
