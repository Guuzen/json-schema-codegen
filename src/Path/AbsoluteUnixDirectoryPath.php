<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Path;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use League\Uri\Uri;

final readonly class AbsoluteUnixDirectoryPath
{
    public string $value;

    public function __construct(string $path)
    {
        if (!str_starts_with($path, '/')) {
            throw new \InvalidArgumentException(sprintf('Path must be absolute: "%s"', $path));
        }
        $this->value = rtrim($path, '/') . '/';
    }

    public function resolve(RelativeUnixPath $relativePath): AbsoluteUnixPath
    {
        return new AbsoluteUnixPath($this->value . $relativePath->value);
    }

    public function toUri(): AbsoluteUri
    {
        return new AbsoluteUri(Uri::fromUnixPath($this->value)->normalize()->toString());
    }
}
