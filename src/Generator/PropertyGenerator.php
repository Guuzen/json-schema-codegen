<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

/**
 * @template TReturn
 */
interface PropertyGenerator
{
    /**
     * @return TReturn
     */
    public function generate(Schema $schema);
}
