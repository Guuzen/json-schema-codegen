<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Nette\PhpGenerator\Method;

final readonly class NetteConstructor
{
    public function __construct(
        private Method $constructor,
    )
    {
    }

    public function addParameters(
        Schema $schema,
        RefNames $refNames,
        GeneratorTools $tools,
    ): void
    {
        $required = [];
        $optional = [];

        foreach ($schema->properties ?? [] as $propertyName => $propertySchema) {
            if ($propertySchema->required) {
                $required[$propertyName] = $propertySchema;
                continue;
            }

            $optional[$propertyName] = $propertySchema;
        }

        foreach ($required as $propertyName => $propertySchema) {
            $this->addParameter($refNames, $propertyName, $propertySchema, $tools);
        }

        foreach ($optional as $propertyName => $propertySchema) {
            $this->addParameter($refNames, $propertyName, $propertySchema, $tools);
        }
    }

    public function addParameter(
        RefNames $refNames,
        string $name,
        Schema $schema,
        GeneratorTools $tools,
    ): void
    {
        $parameter = $this->constructor->addPromotedParameter($name);

        $tools->addComment($parameter, $schema);
        $parameter->addComment('');
        $tools->addAnnotation($parameter, $schema, $refNames);
        $parameter->addComment('');
        $tools->addDefaultValue($parameter, $schema, $refNames);
    }
}
