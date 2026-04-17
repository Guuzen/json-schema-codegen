<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Schema\Keyword;

final readonly class DefaultValue
{
    public function __construct(public mixed $value)
    {
    }
}
