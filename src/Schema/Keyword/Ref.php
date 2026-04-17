<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Schema\Keyword;

use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

final readonly class Ref
{
    public function __construct(public AbsoluteUri $uri)
    {
    }
}
