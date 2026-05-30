<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\ConstructorParameterOrder;
use Guuzen\JsonSchemaCodegen\Generator\FileGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\Generator\SchemaTree;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\Printer;

final readonly class ClassGenerator implements FileGenerator
{
    /**
     * @param iterable<PropertyModifier<PropertyContext>> $modifiers
     */
    public function __construct(
        private Printer $printer,
        private CreatePhpFile $createPhpFile,
        private ConstructorParameterOrder $constructorParameterOrder,
        private iterable $modifiers,
    )
    {
    }

    public function generate(AbsoluteUri $schemaUri, SchemaTree $tree): ?string
    {
        if ($tree->schema->type !== SchemaType::Object) {
            return null;
        }

        [$file, $namespace, $class, $constructor] = $this->createPhpFile->constructorPromoted($schemaUri, $tree);

        foreach ($this->constructorParameterOrder->order($tree) as $propertyName => $propertySchema) {
            $parameter = $constructor->addPromotedParameter($propertyName);
            $context = new PropertyContext($propertySchema, $namespace, $parameter);
            foreach ($this->modifiers as $modifier) {
                $modifier->modify($context);
            }
        }

        return $this->printer->printFile($file);
    }
}
