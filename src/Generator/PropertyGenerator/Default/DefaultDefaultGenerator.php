<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\SchemaTree;

final readonly class DefaultDefaultGenerator implements DefaultGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
    ) {
    }

    public function generate(SchemaTree $tree): ?DefaultValue
    {
        $schemaDefault = $tree->schema->default;
        if ($schemaDefault === null) {
            return null;
        }

        if ($tree->schema->ref !== null && is_array($schemaDefault->value)) {
            $fqcn = $this->fqcnResolver->fromUri($tree->schema->ref->uri);
            $alias = $tree->schema->xAlias ?? $fqcn->className();

            return new DefaultValue(new NewObjectDefaultValue($alias, $schemaDefault->value));
        }

        return new DefaultValue($schemaDefault->value);
    }
}
