<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

use Guuzen\JsonSchemaCodegen\Undefined;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\AnnotationResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

/**
 * @implements PropertyGenerator<ResolvedAnnotation>
 */
final readonly class AnnotationGenerator implements PropertyGenerator
{
    /**
     * @param list<AnnotationResolver> $resolvers
     */
    public function __construct(
        private array $resolvers,
    ) {
    }

    public static function default(FqcnResolver $fqcnResolver, SchemaRegistry $registry): self
    {
        return new self([
            new EnumAnnotation(),
            new UnionAnnotation(),
            new StringAnnotation(),
            new IntegerAnnotation(),
            new NumberAnnotation(),
            new BooleanAnnotation(),
            new ArrayAnnotation(),
            new ObjectAnnotation(),
            new NullAnnotation(),
            new RefObjectAnnotation($fqcnResolver, $registry),
            new InlineRefAnnotation($registry),
        ]);
    }

    public function generate(Schema $schema): ResolvedAnnotation
    {
        foreach ($this->resolvers as $resolver) {
            $resolved = $resolver->resolve($schema, $this);
            if ($resolved !== null) {
                return $this->resolveOptional($resolved, $schema);
            }
        }

        return $this->resolveOptional(new ResolvedAnnotation('mixed', []), $schema);
    }

    private function resolveOptional(ResolvedAnnotation $resolved, Schema $schema): ResolvedAnnotation
    {
        if ($schema->required || $schema->default !== null) {
            return $resolved;
        }

        if ($resolved->annotation === 'mixed' || str_contains($resolved->annotation, 'Undefined')) {
            return $resolved;
        }

        return new ResolvedAnnotation(
            $resolved->annotation . '|Undefined',
            [...$resolved->imports, ['alias' => 'Undefined', 'fqcn' => Undefined::class]],
        );
    }
}
