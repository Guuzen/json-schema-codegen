<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;

final readonly class ScalarResolvedType
{
    public function __construct(
        public string $type,
    ) {
    }
}
