<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\ResolvedAnnotation;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpType\PhpType;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpTypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyModifier;

/**
 * @implements PropertyModifier<PropertyContext>
 */
final readonly class AnnotationModifier implements PropertyModifier
{
    /**
     * @param PropertyGenerator<ResolvedAnnotation, array{type: PhpType}> $generator
     */
    public function __construct(
        private PropertyGenerator $generator,
        private PhpTypeGenerator $phpTypeGenerator,
    )
    {
    }

    public function modify(object $context): void
    {
        $type = $this->phpTypeGenerator->generate($context->propertySchema);

        $resolved = $this->generator->generate($context->propertySchema, ['type' => $type]);

        foreach ($resolved->imports as $import) {
            $context->namespace->addUse($import['fqcn'], $import['alias']);
        }

        $context->parameter->addComment('@var ' . $resolved->annotation);
        $context->parameter->addComment('');
    }
}
