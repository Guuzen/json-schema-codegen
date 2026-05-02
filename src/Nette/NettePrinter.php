<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Nette\PhpGenerator\PsrPrinter;

final class NettePrinter extends PsrPrinter
{
    /**
     * Overrides the default logic to force each attribute onto its own line.
     */
    protected function printAttributes(array $attrs, bool $inline = false): string
    {
        return parent::printAttributes($attrs, inline: false);
    }
}
