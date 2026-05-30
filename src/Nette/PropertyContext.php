<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\SchemaTree;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpNamespace;

final readonly class PropertyContext
{
    public function __construct(
        public SchemaTree $tree,
        public PhpNamespace $namespace,
        public Parameter $parameter,
    )
    {
    }
}
