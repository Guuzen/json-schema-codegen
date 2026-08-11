<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\ClassType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\IntersectionType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\IntType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\ListType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\LiteralIntType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\LiteralStringType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\OtherScalarType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\StringType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\PhpDocType;
use Guuzen\JsonSchemaCodegen\Generator\PhpDocType\UnionType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class DefaultAnnotationGenerator implements AnnotationGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
    )
    {
    }

    public function generate(Schema $schema, RefNames $refNames): string
    {
        $type = $this->render($schema, $refNames);

        return $type->simplify()->render();
    }

    private function render(?Schema $schema, RefNames $refNames): PhpDocType
    {
        if ($schema === null) {
            return OtherScalarType::mixed();
        }

        $allAnnotations = [];

        $allAnnotations[] = $this->renderRef($schema, $refNames);
        $allAnnotations[] = $this->renderAnyOf($schema, $refNames);
        $allAnnotations[] = $this->renderEnum($schema);
        $allAnnotations[] = $this->renderTypes($schema, $refNames);

        return new IntersectionType($allAnnotations);
    }

    private function renderAnyOf(Schema $schema, RefNames $refNames): PhpDocType
    {
        if ($schema->anyOf === null) {
            return OtherScalarType::mixed();
        }

        $annotations = [];
        foreach ($schema->anyOf as $anyOfSchema) {
            $annotations[] = $this->render($anyOfSchema, $refNames);
        }

        if ($annotations === []) {
            return OtherScalarType::mixed();
        }

        return new UnionType($annotations);
    }

    private function renderEnum(Schema $schema): PhpDocType
    {
        if ($schema->enum === [] || $schema->enum === null) {
            return OtherScalarType::mixed();
        }

        $literals = array_map(
            fn(string|int $v) => is_string($v) ? new LiteralStringType($v) : new LiteralIntType($v),
            $schema->enum,
        );

        return new UnionType($literals);
    }

    private function renderTypes(Schema $schema, RefNames $refNames): PhpDocType
    {
        $annotations = [];

        $types = is_array($schema->type) ? $schema->type : [$schema->type];

        foreach ($types as $type) {
            $typeAnnotation = match ($type) {
                SchemaType::Integer => $this->renderInt($schema->minimum, $schema->maximum),
                SchemaType::String => $this->renderString($schema),
                SchemaType::Number => OtherScalarType::float(),
                SchemaType::Boolean => OtherScalarType::bool(),
                SchemaType::Object => OtherScalarType::object(),
                SchemaType::Null => OtherScalarType::null(),
                SchemaType::Array => $this->renderList($schema->items, $schema->minItems, $refNames),
                default => OtherScalarType::mixed(),
            };

            $annotations[] = $typeAnnotation;
        }

        if ($annotations === []) {
            return OtherScalarType::mixed();
        }

        return new UnionType($annotations);
    }

    private function renderRef(Schema $schema, RefNames $refNames): PhpDocType
    {
        $ref = $schema->ref;
        if ($ref === null) {
            return OtherScalarType::mixed();
        }

        $fqcn = $this->fqcnResolver->fromUri($ref->uri);

        return new ClassType($refNames->name($fqcn));
    }

    private function renderInt(?int $minimum, ?int $maximum): PhpDocType
    {
        if ($minimum === null && $maximum === null) {
            return new IntType(null, null);
        }

        return new IntType($minimum, $maximum);
    }

    private function renderString(Schema $schema): PhpDocType
    {
        if ($schema->minLength !== null && $schema->minLength >= 1) {
            return StringType::nonEmptyString();
        }

        return StringType::string();
    }

    private function renderList(?Schema $items, ?int $minItems, RefNames $refNames): PhpDocType
    {
        $elementType = $this->render($items, $refNames);

        return $minItems !== null && $minItems >= 1 ? ListType::nonEmptyList($elementType) : ListType::list($elementType);
    }
}
