<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Uri;

use League\Uri\Uri;

final class RefUriResolver
{
    public function resolve(string $ref, AbsoluteUri $schemaUri): AbsoluteUri
    {
        if (str_contains($ref, '#')) {
            throw new \InvalidArgumentException('JSON Schema ref fragments are not supported');
        }

        try {
            return new AbsoluteUri(Uri::new($schemaUri->value)->resolve($ref)->normalize()->toString());
        } catch (\Throwable $exception) {
            throw new \InvalidArgumentException(
                sprintf('Failed to resolve ref "%s" against schema URI "%s"', $ref, $schemaUri->value),
                previous: $exception,
            );
        }
    }
}
