<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\ResolvedAnnotation;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class AnnotationModifier implements PropertyModifier
{
    /**
     * @param PropertyGenerator<ResolvedAnnotation> $generator
     */
    public function __construct(
        private PropertyGenerator $generator,
    )
    {
    }

    public function modify(object $context): void
    {
        $resolved = $this->generator->generate($context->propertySchema);

        foreach ($resolved->imports as $import) {
            $context->namespace->addUse($import['fqcn'], $import['alias']);
        }

        $context->parameter->addComment('@var ' . $resolved->annotation);
        $context->parameter->addComment('');
    }
}
