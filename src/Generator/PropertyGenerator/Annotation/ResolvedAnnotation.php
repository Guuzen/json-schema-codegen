<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

final readonly class ResolvedAnnotation
{
    /**
     * @param list<array{alias: string, fqcn: string}> $imports
     */
    public function __construct(
        public string $annotation,
        public array $imports,
    ) {
    }
}
