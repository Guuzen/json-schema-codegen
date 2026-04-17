<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

interface SchemaDecoder
{
    /**
     * @return array<string, mixed>
     */
    public function decode(string $content): array;
}
