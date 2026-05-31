<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

final readonly class DefaultDefaultGenerator implements DefaultGenerator
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
        private AbsoluteUri $undefinedUri,
    ) {
    }

    public function generate(Schema $schema): ?DefaultValue
    {
        if ($schema->required) {
            return null;
        }

        foreach ($schema->anyOf ?? [] as $branch) {
            if ($branch->ref === null) {
                continue;
            }

            if ($branch->ref->uri->value === $this->undefinedUri->value) {
                return $this->defaultFromUri($this->undefinedUri, [], null);
            }
        }

        $schemaDefault = $schema->default;

        if ($schemaDefault === null) {
            return null;
        }

        if ($schema->ref !== null && is_array($schemaDefault->value)) {
            return $this->defaultFromUri($schema->ref->uri, $schemaDefault->value, $schema->xAlias);
        }

        return new DefaultValue($schemaDefault->value);
    }

    /**
     * @param array<array-key, mixed> $defaultValue
     */
    private function defaultFromUri(AbsoluteUri $uri, array $defaultValue, ?string $alias): DefaultValue
    {
        $fqcn = $this->fqcnResolver->fromUri($uri);
        $alias = $alias ?? $fqcn->className();

        return new DefaultValue(new NewObjectDefaultValue($alias, $defaultValue));
    }
}
