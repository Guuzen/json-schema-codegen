<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

final readonly class CreatePhpFile
{
    public function __construct(
        private FqcnResolver $fqcnResolver,
    ) {
    }

    /**
     * @return array{0: PhpFile, 1: PhpNamespace, 2:ClassType, 3: Method}
     */
    public function constructorPromoted(AbsoluteUri $schemaUri, Schema $schema): array
    {
        $fqcn = $this->fqcnResolver->fromUri($schemaUri);

        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($fqcn->namespace());
        $class = $namespace->addClass($fqcn->className());

        if ($schema->description !== null) {
            $class->addComment($schema->description);
        }

        $constructor = $class->addMethod('__construct');
        $class->setFinal();

        return [$file, $namespace, $class, $constructor];
    }
}
