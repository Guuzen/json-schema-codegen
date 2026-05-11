<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation;

use Guuzen\JsonSchemaCodegen\Schema\Schema;

interface ConstraintGenerator
{
    /**
     * @return list<Constraint>
     */
    public function generate(Schema $schema): array;
}
