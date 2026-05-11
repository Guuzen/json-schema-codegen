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
     * Branches are collected and concatenated, so callers AND them via stacked
     * Symfony attributes. This is wrong for anyOf in the general case, but
     * works for every shape currently in use: nullable wrappers (null branch
     * contributes nothing and Symfony skips most constraints on null) and
     * type unions (collapsed into a single Type constraint upstream). If a
     * schema ever lands with two parallel branches both carrying non-type
     * constraints, wrap the per-branch results in Symfony's AtLeastOneOf here.
     *
     * @return list<Constraint>
     */
    public function anyOf(Schema $schema, ConstraintGenerator $generator): array
    {
        $constraints = [];

        if ($schema->anyOf !== null) {
            foreach ($schema->anyOf as $branch) {
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
