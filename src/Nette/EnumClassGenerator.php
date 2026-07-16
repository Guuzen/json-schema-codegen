<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\FileGenerator;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Printer;

final class EnumClassGenerator implements FileGenerator
{
    public function __construct(
        private Printer $printer,
        private FqcnResolver $fqcnResolver,
    )
    {
    }

    public function generate(AbsoluteUri $schemaUri, Schema $schema): ?string
    {
        $cases = self::onlyStringsEnum($schema->enum);

        if ($cases === null) {
            return null;
        }

        $fqcn = $this->fqcnResolver->fromUri($schemaUri);

        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($fqcn->namespace());
        $enum = $namespace->addEnum($fqcn->className());

        if ($schema->description !== null) {
            $enum->addComment($schema->description);
        }

        foreach ($cases as $enumCase) {
            $enum->addCase($enumCase, $enumCase);
        }

        return $this->printer->printFile($file);
    }

    /**
     * @param list<string|int>|null $enum
     *
     * @return list<string>|null
     */
    private static function onlyStringsEnum(?array $enum): ?array
    {
        if ($enum === null) {
            return null;
        }

        $cases = [];

        foreach ($enum as $case) {
            if (is_string($case) && !is_numeric($case)) {
                $cases[] = $case;
            } else {
                return null;
            }
        }

        return $cases;
    }
}
