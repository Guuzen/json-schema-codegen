<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

/**
 * @implements PropertyGenerator<ScalarResolvedType|ClassResolvedType|null>
 */
final readonly class TypeGenerator implements PropertyGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
        private SchemaRegistry $registry,
    ) {
    }

    public function generate(Schema $schema): ScalarResolvedType|ClassResolvedType|null
    {
        if ($schema->ref !== null) {
            $referencedSchema = $this->registry->get($schema->ref->uri);

            if ($referencedSchema->type === SchemaType::Object) {
                $fqcn = $this->fqcnResolver->fromUri($schema->ref->uri);

                return new ClassResolvedType($fqcn->fqcn, $schema->title ?? $fqcn->className());
            }

            return $this->generate($referencedSchema);
        }

        if ($schema->oneOf !== null) {
            $nonNullBranches = array_values(array_filter(
                $schema->oneOf,
                static fn(Schema $branch): bool => $branch->type !== SchemaType::Null,
            ));

            return count($nonNullBranches) === 1 ? $this->generate($nonNullBranches[0]) : null;
        }

        if (is_array($schema->type)) {
            $nonNullTypes = array_values(array_filter(
                $schema->type,
                static fn(SchemaType $type): bool => $type !== SchemaType::Null,
            ));

            return count($nonNullTypes) === 1 ? $this->generate(new Schema(type: $nonNullTypes[0])) : null;
        }

        return match ($schema->type) {
            SchemaType::String => new ScalarResolvedType('string'),
            SchemaType::Integer => new ScalarResolvedType('integer'),
            SchemaType::Number => new ScalarResolvedType('float'),
            SchemaType::Boolean => new ScalarResolvedType('bool'),
            SchemaType::Array => new ScalarResolvedType('array'),
            SchemaType::Object => new ScalarResolvedType('object'),
            default => null,
        };
    }
}
