<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\ConstructorParameterOrder;
use Guuzen\JsonSchemaCodegen\Generator\FileGenerator;
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
    )
    {
    }

    public function generate(AbsoluteUri $schemaUri, Schema $schema): ?string
    {
        if ($schema->type !== SchemaType::Object) {
            return null;
        }

        [$file, $namespace, $class, $constructor] = $this->createPhpFile->constructorPromoted($schemaUri);

        if ($schema->description !== null) {
            $class->addComment($schema->description);
        }

        foreach ($this->constructorParameterOrder->order($schema) as $propertyName => $propertySchema) {
            $this->promotedParameter->add($constructor, $namespace, $propertyName, $propertySchema);
        }

        return $this->printer->printFile($file);
    }
}
