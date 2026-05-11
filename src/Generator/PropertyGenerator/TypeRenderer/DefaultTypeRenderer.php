<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Undefined;

final readonly class DefaultTypeRenderer implements TypeRenderer
{
    public function __construct(
        private FqcnResolver   $fqcnResolver,
        private SchemaRegistry $registry,
    ) {
    }

    public function render(Schema $schema): array
    {
        $rendered = $this->renderRequired($schema);

        if ($schema->required || $schema->default !== null || $rendered === []) {
            return $rendered;
        }

        $rendered[] = new RenderedType('Undefined', ['alias' => 'Undefined', 'fqcn' => Undefined::class]);
        return $rendered;
    }

    /**
     * @return list<RenderedType>
     */
    private function renderRequired(Schema $schema): array
    {
        if ($schema->ref !== null) {
            $referenced = $this->registry->get($schema->ref->uri);

            if ($referenced->type === SchemaType::Object) {
                $fqcn  = $this->fqcnResolver->fromUri($schema->ref->uri);
                $alias = $schema->xAlias ?? $fqcn->className();
                return [new RenderedType($alias, ['alias' => $alias, 'fqcn' => $fqcn->fqcn])];
            }

            return $this->renderRequired($referenced);
        }

        if ($schema->anyOf !== null) {
            return $this->renderBranches($schema->anyOf);
        }

        if (is_array($schema->type)) {
            return $this->renderBranches(array_map(
                fn(SchemaType $t) => new Schema(type: $t),
                $schema->type,
            ));
        }

        if ($schema->enum !== null) {
            return $this->renderEnum($schema->enum);
        }

        return match ($schema->type) {
            SchemaType::String  => [new RenderedType('string')],
            SchemaType::Integer => [new RenderedType('integer')],
            SchemaType::Number  => [new RenderedType('float')],
            SchemaType::Boolean => [new RenderedType('bool')],
            SchemaType::Object  => [new RenderedType('object')],
            SchemaType::Array   => [new RenderedType('list')],
            SchemaType::Null    => [],
            default             => [],
        };
    }

    /**
     * @param list<Schema> $branches
     * @return list<RenderedType>
     */
    private function renderBranches(array $branches): array
    {
        $results = [];
        foreach ($branches as $branch) {
            if ($branch->type === SchemaType::Null) {
                continue;
            }
            foreach ($this->renderRequired($branch) as $rendered) {
                $results[] = $rendered;
            }
        }
        return $results;
    }

    /**
     * @param list<string|int> $values
     * @return list<RenderedType>
     */
    private function renderEnum(array $values): array
    {
        $types = [];
        if (array_filter($values, 'is_string') !== []) {
            $types[] = new RenderedType('string');
        }
        if (array_filter($values, 'is_int') !== []) {
            $types[] = new RenderedType('integer');
        }
        return $types;
    }
}
