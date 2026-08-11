<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation;

final readonly class ResolvedAnnotation
{
    public function __construct(
        public string $annotation,
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
            function (ResolvedAnnotation $carry, ResolvedAnnotation $item) {

                if ($carry->annotation === $item->annotation) {
                    return new self($carry->annotation);
                }

                return new self(
                    annotation: $carry->annotation . '|' . $item->annotation,
                );
            },
            $initial,
        );
    }

    public static function mixed(): self
    {
        return new self(annotation: 'mixed');
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
