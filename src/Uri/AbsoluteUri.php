<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Uri;

use League\Uri\Uri;

final readonly class AbsoluteUri
{
    public function __construct(
        public string $value,
    ) {
        try {
            $uri = Uri::new($value);
        } catch (\Throwable $exception) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not an absolute URI', $value),
                previous: $exception,
            );
        }

        if (!$uri->isAbsolute()) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not an absolute URI: missing scheme', $value)
            );
        }

        if ($uri->getAuthority() === null && $uri->getPath() === '') {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not an absolute URI: missing authority and path', $value)
            );
        }
    }
}
