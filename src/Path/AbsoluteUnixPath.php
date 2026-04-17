<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Path;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use League\Uri\Uri;

final readonly class AbsoluteUnixPath
{
    public function __construct(
        public string $value,
    ) {
        if (!str_starts_with($value, '/')) {
            throw new \InvalidArgumentException(sprintf('Path must be absolute: "%s"', $value));
        }
    }

    public function relativeTo(AbsoluteUnixDirectoryPath $base): RelativeUnixPath
    {
        if (!str_starts_with($this->value, $base->value)) {
            throw new \InvalidArgumentException(
                sprintf('Path "%s" is outside base path "%s"', $this->value, $base->value)
            );
        }

        return new RelativeUnixPath(substr($this->value, strlen($base->value)));
    }

    public function toUri(): AbsoluteUri
    {
        return new AbsoluteUri(Uri::fromUnixPath($this->value)->normalize()->toString());
    }
}
