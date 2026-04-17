<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Fqcn;

final readonly class Fqcn
{
    public function __construct(
        public string $fqcn,
    ) {
        if ($fqcn === '') {
            throw new \InvalidArgumentException('FQCN cannot be empty');
        }

        if (str_ends_with($fqcn, '\\')) {
            throw new \InvalidArgumentException(sprintf('FQCN "%s" has no class name', $fqcn));
        }
    }

    public function className(): string
    {
        $pos = strrpos($this->fqcn, '\\');

        return $pos === false ? $this->fqcn : substr($this->fqcn, $pos + 1);
    }

    public function namespace(): string
    {
        return substr($this->fqcn, 0, (int) strrpos($this->fqcn, '\\'));
    }
}
