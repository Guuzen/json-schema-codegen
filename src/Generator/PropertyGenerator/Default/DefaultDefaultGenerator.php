<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class DefaultDefaultGenerator implements DefaultGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
    ) {
    }

    public function generate(Schema $schema): ?DefaultValue
    {
        $schemaDefault = $schema->default;
        if ($schemaDefault === null) {
            return null;
        }

        if ($schema->ref !== null && is_array($schemaDefault->value)) {
            $fqcn = $this->fqcnResolver->fromUri($schema->ref->uri);
            $alias = $schema->xAlias ?? $fqcn->className();

            return new DefaultValue(new NewObjectDefaultValue($alias, $schemaDefault->value));
        }

        return new DefaultValue($schemaDefault->value);
    }
}
