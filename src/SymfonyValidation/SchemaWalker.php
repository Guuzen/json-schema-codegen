<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\SymfonyValidation;

use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\Schema;

final readonly class SchemaWalker
{
    public function __construct(
        private SchemaRegistry $registry,
    )
    {
    }

    /**
     * TODO branches are collected and concatenated, which means callers AND
     *
     * them via stacked Symfony attributes. Correct for allOf-style composition
     * but wrong for true oneOf (XOR) and anyOf (OR). Fix later by dispatching
     * on the combinator or emitting a composite constraint (AtLeastOneOf for
     * anyOf, a custom OneOf for oneOf).
     *
     * @return list<Constraint>
     */
    public function oneOf(Schema $schema, ConstraintGenerator $generator): array
    {
        $constraints = [];

        if ($schema->oneOf !== null) {
            foreach ($schema->oneOf as $branch) {
                $constraints = [
                    ...$constraints,
                    ...$generator->generate($branch),
                ];
            }
        }

        return $constraints;
    }

    /**
     * TODO fix endlress recursion
     *
     * @return list<Constraint>
     */
    public function ref(Schema $schema, ConstraintGenerator $generator): array
    {
        if ($schema->ref !== null) {
            $refSchema = $this->registry->get($schema->ref->uri);

            if ($refSchema->isObject()) {
                return [];
            }

            return $generator->generate($refSchema);
        }

        return [];
    }
}
