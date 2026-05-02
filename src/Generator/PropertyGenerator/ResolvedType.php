<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

final readonly class ResolvedType
{
    /**
     * @param array{alias: string, fqcn: string}|null $import
     */
    public function __construct(
        public string $type,
        public ?array $import = null,
    )
    {
    }
}
