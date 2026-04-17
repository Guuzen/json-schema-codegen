<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Fqcn;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use League\Uri\Uri;

final readonly class FqcnResolver
{
    /**
     * @param non-empty-string $suffix
     */
    public function __construct(
        private AbsoluteUri $baseUri,
        private string $baseNamespace,
        private string $suffix,
    ) {
    }

    public function fromUri(AbsoluteUri $uri): Fqcn
    {
        $relativePath = $this->relativePath($uri);
        $withoutExtension = $relativePath;
        if (str_ends_with($withoutExtension, $this->suffix)) {
            $withoutExtension = substr($withoutExtension, 0, -strlen($this->suffix));
        }
        $parts = explode('/', $withoutExtension);

        $lastPart = end($parts);
        $className = $lastPart;

        array_pop($parts);
        $namespaceParts = $parts;

        $fqcn = $this->baseNamespace;
        if ($namespaceParts !== []) {
            $fqcn .= '\\' . implode('\\', $namespaceParts);
        }
        $fqcn .= '\\' . $className;

        return new Fqcn(fqcn: $fqcn);
    }

    private function relativePath(AbsoluteUri $uri): string
    {
        $target = Uri::new($uri->value)->normalize();
        $base = Uri::new($this->baseUri->value)->normalize();

        if ($target->getQuery() !== null || $target->getFragment() !== null) {
            throw new \InvalidArgumentException(sprintf('Cannot derive FQCN from URI with query or fragment: "%s"', $uri->value));
        }

        if ($target->getScheme() !== $base->getScheme() || $target->getAuthority() !== $base->getAuthority()) {
            throw new \InvalidArgumentException(sprintf('URI "%s" is outside base URI "%s"', $uri->value, $this->baseUri->value));
        }

        $basePath = rtrim($base->getPath(), '/') . '/';
        $targetPath = $target->getPath();

        if (!str_starts_with($targetPath, $basePath)) {
            throw new \InvalidArgumentException(sprintf('URI "%s" is outside base URI "%s"', $uri->value, $this->baseUri->value));
        }

        return substr($targetPath, strlen($basePath));
    }
}
