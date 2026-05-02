<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeRenderer;

final readonly class RenderedType
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
