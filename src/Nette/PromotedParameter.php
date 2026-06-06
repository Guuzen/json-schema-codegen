<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

/**
 * Adds a promoted constructor parameter for a schema and runs the property
 * modifiers over it (type annotation, comment, default value).
 */
final readonly class PromotedParameter
{
    /**
     * @param iterable<PropertyModifier<PropertyContext>> $modifiers
     */
    public function __construct(
        private iterable $modifiers,
    )
    {
    }

    public function add(Method $constructor, PhpNamespace $namespace, RefNames $refNames, string $name, Schema $schema): void
    {
        $parameter = $constructor->addPromotedParameter($name);
        $context = new PropertyContext($schema, $namespace, $parameter, $refNames);
        foreach ($this->modifiers as $modifier) {
            $modifier->modify($context);
        }
    }
}
