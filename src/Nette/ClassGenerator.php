<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\ConstructorParameterOrder;
use Guuzen\JsonSchemaCodegen\Generator\FileGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\PsrPrinter;

final readonly class ClassGenerator implements FileGenerator
{
    /**
     * @param iterable<PropertyModifier<PropertyContext>> $modifiers
     */
    public function __construct(
        private CreatePhpFile $createPhpFile,
        private ConstructorParameterOrder $constructorParameterOrder,
        private iterable $modifiers,
    )
    {
    }

    public function generate(AbsoluteUri $schemaUri, Schema $schema): ?string
    {
        if ($schema->type !== SchemaType::Object) {
            return null;
        }

        [$file, $namespace, $constructor] = $this->createPhpFile->constructorPromoted($schemaUri, $schema);

        foreach ($this->constructorParameterOrder->order($schema) as $propertyName => $propertySchema) {
            $parameter = $constructor->addPromotedParameter($propertyName);
            $context = new PropertyContext($propertySchema, $namespace, $parameter);
            foreach ($this->modifiers as $modifier) {
                $modifier->modify($context);
            }
        }

        return new PsrPrinter()->printFile($file);
    }
}
