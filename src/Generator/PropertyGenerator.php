<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

/**
 * @template TReturn
 * @template TParams
 */
interface PropertyGenerator
{
    /**
     * @param TParams $params
     *
     * @return TReturn
     */
    public function generate(Schema $schema, mixed $params);
}
