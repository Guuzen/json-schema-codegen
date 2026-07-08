<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

/**
 * @template Context
 *
 * @implements DefaultGenerator<Context>
 */
final readonly class DefaultDefaultGenerator implements DefaultGenerator
{
    /**
     * @param DefaultValueFactory<Context> $defaultValueFactory
     */
    public function __construct(
        private FqcnResolver $fqcnResolver,
        private AbsoluteUri $undefinedUri,
        private DefaultValueFactory $defaultValueFactory,
    ) {
    }

    public function generate(Schema $schema, RefNames $refNames): AddDefaultValue
    {
        if ($schema->required) {
            return $this->defaultValueFactory->noDefaultValue();
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
            return $this->defaultValueFactory->noDefaultValue();
        }

        if ($schema->ref !== null && is_array($schemaDefault->value)) {
            return $this->defaultFromUri($schema->ref->uri, $schemaDefault->value, $refNames);
        }

        return $this->defaultValueFactory->defaultValue($schemaDefault->value);
    }

    /**
     * @param array<array-key, mixed> $defaultValue
     *
     * @return AddDefaultValue<Context>
     */
    private function defaultFromUri(AbsoluteUri $uri, array $defaultValue, RefNames $refNames): AddDefaultValue
    {
        $fqcn = $this->fqcnResolver->fromUri($uri);

        return $this->defaultValueFactory->newObjectDefaultValue($refNames->name($fqcn), $defaultValue);
    }
}
