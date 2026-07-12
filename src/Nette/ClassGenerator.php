<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
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
        private FileImportsFactory $fileImportsFactory,
        private FqcnResolver $fqcnResolver,
        private GeneratorTools $tools,
    )
    {
    }

    public function generate(AbsoluteUri $schemaUri, Schema $schema): ?string
    {
        if ($schema->type !== SchemaType::Object) {
            return null;
        }

        $class = NetteClass::create($schemaUri, $this->fqcnResolver);

        $fileImports = $this->fileImportsFactory->forFile($schemaUri, $schema);

        $class->addUses($fileImports);

        $class->addComment($schema->description);
        $class->addComment('');
        $class->addComment('@immutable');

        $constructor = $class->addConstructor();

        $constructor->addParameters($schema, $fileImports, $this->tools);

        return $class->render($this->printer);
    }
}
