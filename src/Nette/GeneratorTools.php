<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\AnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\CommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Nette\PhpGenerator\Parameter;

final readonly class GeneratorTools
{
    /**
     * @param CommentGenerator<Parameter> $commentGenerator
     * @param DefaultGenerator<Parameter> $defaultGenerator
     */
    public function __construct(
        private AnnotationGenerator $annotationGenerator,
        private CommentGenerator $commentGenerator,
        private DefaultGenerator $defaultGenerator,
    )
    {
    }

    public function addAnnotation(Parameter $parameter, Schema $schema, RefNames $refNames): void
    {
        $resolved = $this->annotationGenerator->generate($schema, $refNames);

        $parameter->addComment('@var ' . $resolved->annotation);
    }

    public function addComment(Parameter $parameter, Schema $schema): void
    {
        $comment = $this->commentGenerator->generate($schema);
        $comment->addTo($parameter);
    }

    public function addDefaultValue(Parameter $parameter, Schema $schema, RefNames $refNames): void
    {
        $default = $this->defaultGenerator->generate($schema, $refNames);
        $default->addTo($parameter);
    }
}
