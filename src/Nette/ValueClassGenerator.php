<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\FileGenerator;
use Guuzen\JsonSchemaCodegen\Generator\FileImportsFactory;
use Guuzen\JsonSchemaCodegen\Schema\Keyword\SchemaType;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\Printer;

/**
 * Generates a single-value object for any non-object schema (scalar, enum, array,
 * union, $ref): one promoted `value` parameter wrapping the schema's rendered type.
 * Declines object schemas, which {@see ClassGenerator} handles.
 */
final readonly class ValueClassGenerator implements FileGenerator
{
    public function __construct(
        private Printer $printer,
        private CreatePhpFile $createPhpFile,
        private PromotedParameter $promotedParameter,
        private FileImportsFactory $fileImportsFactory,
    )
    {
    }

    public function generate(AbsoluteUri $schemaUri, Schema $schema): ?string
    {
        if ($schema->type === SchemaType::Object) {
            return null;
        }

        [$file, $namespace, , $constructor] = $this->createPhpFile->constructorPromoted($schemaUri);

        $fileImports = $this->fileImportsFactory->forFile($schemaUri, $schema);
        foreach ($fileImports->uses() as $use) {
            $namespace->addUse($use->fqcn);
        }

        $this->promotedParameter->add($constructor, $namespace, $fileImports, 'value', $schema);

        return $this->printer->printFile($file);
    }
}
