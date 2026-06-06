<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Fqcn\Fqcn;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\RefNames;

/**
 * Decides, once per generated file, how each referenced DTO is named inside that file.
 */
final readonly class FileImports implements RefNames
{
    /**
     * @param array<string, Fqcn> $imports
     */
    public function __construct(
        private Fqcn $fqcn,
        private array $imports,
    )
    {
    }

    /**
     * The references that must be imported with a `use` statement in this file
     *
     * @return list<Fqcn>
     */
    public function uses(): array
    {
        return array_values(
            array_filter(
                $this->imports,
                fn(Fqcn $fqcn) => $fqcn->namespace() !== $this->fqcn->namespace(),
            )
        );
    }

    public function name(Fqcn $fqcn): string
    {
        // A class in the file's own namespace is always reachable by its bare short name.
        if ($fqcn->namespace() === $this->fqcn->namespace()) {
            return $fqcn->className();
        }

        // An imported (unambiguous) reference is written under its short name.
        if (isset($this->imports[$fqcn->fqcn])) {
            return $fqcn->className();
        }

        // A colliding reference under the file's namespace is written relative to it,
        // otherwise as a leading-backslash fully-qualified name.
        $prefix = $this->fqcn->namespace() . '\\';
        if (str_starts_with($fqcn->fqcn, $prefix)) {
            return substr($fqcn->fqcn, strlen($prefix));
        }

        return '\\' . $fqcn->fqcn;
    }
}
