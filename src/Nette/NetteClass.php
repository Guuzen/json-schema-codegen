<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\FileImports;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;

final readonly class NetteClass
{
    public function __construct(
        public PhpFile $file,
        public PhpNamespace $namespace,
        public ClassType $class,
    )
    {
    }

    public static function create(AbsoluteUri $schemaUri, FqcnResolver $fqcnResolver): self
    {
        $fqcn = $fqcnResolver->fromUri($schemaUri);

        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($fqcn->namespace());
        $class = $namespace->addClass($fqcn->className());

        $class->setFinal();

        return new self($file, $namespace, $class);
    }

    public function addComment(?string $comment): void
    {
        if ($comment !== null) {
            $this->class->addComment($comment);
        }
    }

    public function addUses(FileImports $imports): void
    {
        foreach ($imports->uses() as $use) {
            $this->namespace->addUse($use->fqcn);
        }
    }

    public function addConstructor(): NetteConstructor
    {
        $constructor = $this->class->addMethod('__construct');

        return new NetteConstructor($constructor);
    }

    public function render(Printer $printer): string
    {
        return $printer->printFile($this->file);
    }
}
