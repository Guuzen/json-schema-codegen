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

    /**
     * @param list<self> $annotations
     */
    public function intersect(array $annotations): self
    {
        return array_reduce(
            $annotations,
            fn (ResolvedAnnotation $carry, ResolvedAnnotation $item) => new self(
                annotation: $carry->annotation . '&' . $item->annotation,
                imports: [...$carry->imports, ...$item->imports],
            ),
            $this,
        );
    }

    /**
     * @param list<self> $annotations
     */
    public function unite(array $annotations): self
    {
        return array_reduce(
            $annotations,
            fn (ResolvedAnnotation $carry, ResolvedAnnotation $item) => new self(
                annotation: $carry->annotation . '|' . $item->annotation,
                imports: [...$carry->imports, ...$item->imports],
            ),
            $this,
        );
    }

    public static function mixed(): self
    {
        return new self(annotation: 'mixed', imports: []);
    }
}
