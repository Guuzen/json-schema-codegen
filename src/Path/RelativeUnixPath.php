<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Path;

final readonly class RelativeUnixPath
{
    public function __construct(
        public string $value,
    ) {
        if (str_starts_with($value, '/')) {
            throw new \InvalidArgumentException(sprintf('Path must be relative: "%s"', $value));
        }
    }
}
