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
     * @param non-empty-array<self> $annotations
     */
    public static function intersect(array $annotations): self
    {
        $initial = array_shift($annotations);

        return array_reduce(
            $annotations,
            fn (ResolvedAnnotation $carry, ResolvedAnnotation $item) => new self(
                annotation: $carry->annotation . '&' . $item->annotation,
                imports: [...$carry->imports, ...$item->imports],
            ),
            $initial,
        );
    }

    /**
     * @param non-empty-array<self> $annotations
     */
    public static function unite(array $annotations): self
    {
        $initial = array_shift($annotations);

        return array_reduce(
            $annotations,
            fn (ResolvedAnnotation $carry, ResolvedAnnotation $item) => new self(
                annotation: $carry->annotation . '|' . $item->annotation,
                imports: [...$carry->imports, ...$item->imports],
            ),
            $initial,
        );
    }

    public static function mixed(): self
    {
        return new self(annotation: 'mixed', imports: []);
    }

    public function isMixed(): bool
    {
        return $this->annotation === 'mixed';
    }

    public function isNotMixed(): bool
    {
        return $this->annotation !== 'mixed';
    }
}
