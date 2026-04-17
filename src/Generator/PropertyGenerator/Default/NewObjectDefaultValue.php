<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default;

final readonly class NewObjectDefaultValue
{
    /**
     * @param array<array-key, mixed> $args
     */
    public function __construct(
        public string $className,
        public array $args,
    ) {
    }
}
