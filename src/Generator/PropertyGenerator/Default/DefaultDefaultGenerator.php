<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

final readonly class DefaultDefaultGenerator implements DefaultGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
        private AbsoluteUri $undefinedUri,
    ) {
    }

    public function generate(Schema $schema, RefNames $refNames): ?DefaultValue
    {
        if ($schema->required) {
            return null;
        }

        foreach ($schema->anyOf ?? [] as $branch) {
            if ($branch->ref === null) {
                continue;
            }

            if ($branch->ref->uri->value === $this->undefinedUri->value) {
                return $this->defaultFromUri($this->undefinedUri, [], $refNames);
            }
        }

        $schemaDefault = $schema->default;

        if ($schemaDefault === null) {
            return null;
        }

        if ($schema->ref !== null && is_array($schemaDefault->value)) {
            return $this->defaultFromUri($schema->ref->uri, $schemaDefault->value, $refNames);
        }

        return new DefaultValue($schemaDefault->value);
    }

    /**
     * @param array<array-key, mixed> $defaultValue
     */
    private function defaultFromUri(AbsoluteUri $uri, array $defaultValue, RefNames $refNames): DefaultValue
    {
        $fqcn = $this->fqcnResolver->fromUri($uri);

        return new DefaultValue(new NewObjectDefaultValue($refNames->name($fqcn), $defaultValue));
    }
}
