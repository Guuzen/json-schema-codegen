<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\ConstructorParameterOrder;
use Guuzen\JsonSchemaCodegen\Generator\FileGenerator;
use Guuzen\JsonSchemaCodegen\Generator\FileImportsFactory;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\Printer;

/**
 * Generates a DTO class for an object schema: one promoted constructor parameter
 * per property. Declines every other schema shape.
 */
final readonly class ClassGenerator implements FileGenerator
{
    public function __construct(
        private Printer $printer,
        private CreatePhpFile $createPhpFile,
        private ConstructorParameterOrder $constructorParameterOrder,
        private PromotedParameter $promotedParameter,
        private FileImportsFactory $fileImportsFactory,
    )
    {
    }

    public function generate(AbsoluteUri $schemaUri, Schema $schema): ?string
    {
        if ($schema->type !== SchemaType::Object) {
            return null;
        }

        [$file, $namespace, $class, $constructor] = $this->createPhpFile->constructorPromoted($schemaUri);

        $fileImports = $this->fileImportsFactory->forFile($schemaUri, $schema);

        foreach ($fileImports->uses() as $use) {
            $namespace->addUse($use->fqcn);
        }

        if ($schema->description !== null) {
            $class->addComment($schema->description);
            $class->addComment('');
        }

        $class->addComment('@immutable');

        foreach ($this->constructorParameterOrder->order($schema) as $propertyName => $propertySchema) {
            $this->promotedParameter->add($constructor, $namespace, $fileImports, $propertyName, $propertySchema);
        }

        return $this->printer->printFile($file);
    }
}
