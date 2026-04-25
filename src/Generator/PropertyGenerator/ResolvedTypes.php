<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

final readonly class ResolvedTypes
{
    /**
     * @param list<string> $types
     * @param list<array{alias: string, fqcn: string}> $imports
     */
    public function __construct(
        public array $types,
        public array $imports,
    ) {
    }
}
