<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpNamespace;

final readonly class PropertyContext
{
    public function __construct(
        public Schema $propertySchema,
        public PhpNamespace $namespace,
        public Parameter $parameter,
    )
    {
    }
}
