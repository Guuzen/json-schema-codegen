<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Generator;

use Guuzen\JsonSchemaCodegen\Schema\SchemaParser;
use Guuzen\JsonSchemaCodegen\Schema\Schema;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

final readonly class FilesGenerator
{
    /**
     * @param iterable<FileGenerator> $generators
     */
    public function __construct(
        private PathCollector $pathCollector,
        private FileLoader $fileLoader,
        private PathTransformer $pathTransformer,
        private FileDumper $fileDumper,
        private SchemaParser $schemaParser,
        private SchemaDecoder $schemaFileDecoder,
        private SchemaRegistry $registry,
        private iterable $generators,
    )
    {
    }

    public function run(): void
    {
        $items = [];
        foreach ($this->pathCollector->collect() as $path) {
            $uri = $path->toUri();
            $schema = $this->schemaParser->parse(
                $this->schemaFileDecoder->decode($this->fileLoader->load($path)),
                $uri,
            );
            $this->registry->add($uri, $schema);
            $items[] = [$path, $uri, $schema];
        }

        foreach ($items as [$path, $uri, $schema]) {
            $phpCode = $this->generate($schema, $uri);
            if ($phpCode === null) {
                continue;
            }
            $this->fileDumper->dump($this->pathTransformer->generate($path), $phpCode);
        }
    }

    private function generate(Schema $schema, AbsoluteUri $schemaUri): ?string
    {
        foreach ($this->generators as $step) {
            $file = $step->generate($schemaUri, $schema);
            if ($file) {
                return $file;
            }
        }

        return null;
    }
}
