<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Filesystem\GetContents;
use Guuzen\JsonSchemaCodegen\Filesystem\OutputPathTransformer;
use Guuzen\JsonSchemaCodegen\Filesystem\PathsWithSuffix;
use Guuzen\JsonSchemaCodegen\Filesystem\PutContents;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\FileDumper;
use Guuzen\JsonSchemaCodegen\Generator\FileGenerator;
use Guuzen\JsonSchemaCodegen\Generator\FileImportsFactory;
use Guuzen\JsonSchemaCodegen\Generator\FileLoader;
use Guuzen\JsonSchemaCodegen\Generator\FilesGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PathCollector;
use Guuzen\JsonSchemaCodegen\Generator\PathTransformer;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\AnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\DefaultAnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\CommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\DefaultCommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultDefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaDecoder;
use Guuzen\JsonSchemaCodegen\Generator\TypeMappings;
use Guuzen\JsonSchemaCodegen\Nette\AddComment\NetteCommentFactory;
use Guuzen\JsonSchemaCodegen\Nette\AddOptionalValue\NetteDefaultValueFactory;
use Guuzen\JsonSchemaCodegen\Path\AbsoluteUnixDirectoryPath;
use Guuzen\JsonSchemaCodegen\Path\RelativeUnixPath;
use Guuzen\JsonSchemaCodegen\Schema\JsonDecoder;
use Guuzen\JsonSchemaCodegen\Schema\SchemaParser;
use Guuzen\JsonSchemaCodegen\Schema\YamlDecoder;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;
use Nette\PhpGenerator\Parameter;

final readonly class NetteFilesGeneratorFactory
{
    /**
     * @param non-empty-string            $schemaSuffix
     * @param array<string, class-string> $typeMappings
     */
    public function __construct(
        private string $baseNamespace,
        private string $schemaPath,
        private string $outputPath,
        private string $schemaSuffix,
        private string $undefinedPath,
        private array $typeMappings = [],
    )
    {
    }

    public function fqcnResolver(): FqcnResolver
    {
        $schemaPath = new AbsoluteUnixDirectoryPath($this->schemaPath);

        return new FqcnResolver(
            $schemaPath->toUri(),
            $this->baseNamespace,
            $this->schemaSuffix,
        );
    }

    public function typeMappings(): TypeMappings
    {
        $schemaPath = new AbsoluteUnixDirectoryPath($this->schemaPath);

        return TypeMappings::create($schemaPath, $this->typeMappings);
    }

    public function undefinedUri(): AbsoluteUri
    {
        $schemaPath = new AbsoluteUnixDirectoryPath($this->schemaPath);

        return $schemaPath->resolve(new RelativeUnixPath($this->undefinedPath))->toUri();
    }

    public function pathCollector(): PathCollector
    {
        $schemaPath = new AbsoluteUnixDirectoryPath($this->schemaPath);

        return PathsWithSuffix::create($schemaPath, $this->schemaSuffix);
    }

    public function pathTransformer(): PathTransformer
    {
        $schemaPath = new AbsoluteUnixDirectoryPath($this->schemaPath);

        return new OutputPathTransformer(
            $schemaPath,
            new AbsoluteUnixDirectoryPath($this->outputPath),
            $this->schemaSuffix,
        );
    }

    public function decoder(): SchemaDecoder
    {
        $suffixExtension = pathinfo($this->schemaSuffix, PATHINFO_EXTENSION);

        return match ($suffixExtension) {
            'json' => new JsonDecoder(),
            'yaml' => new YamlDecoder(),
            default => throw new \InvalidArgumentException(
                'By default only json and yaml files are supported.'
            )
        };
    }

    public function annotationGenerator(): AnnotationGenerator
    {
        return new DefaultAnnotationGenerator($this->fqcnResolver());
    }

    /**
     * @return DefaultCommentGenerator<Parameter>
     */
    public function commentGenerator(): CommentGenerator
    {
        return new DefaultCommentGenerator(new NetteCommentFactory());
    }

    /**
     * @return DefaultGenerator<Parameter>
     */
    public function defaultGenerator(): DefaultGenerator
    {
        return new DefaultDefaultGenerator(
            $this->fqcnResolver(),
            $this->undefinedUri(),
            new NetteDefaultValueFactory(),
        );
    }

    public function generatorTools(): GeneratorTools
    {
        return new GeneratorTools(
            annotationGenerator: $this->annotationGenerator(),
            commentGenerator: $this->commentGenerator(),
            defaultGenerator: $this->defaultGenerator(),
        );
    }

    public function fileImportsFactory(): FileImportsFactory
    {
        return new FileImportsFactory($this->fqcnResolver(), $this->typeMappings());
    }

    public function classGenerator(): ClassGenerator
    {
        return new ClassGenerator(
            printer: new NettePrinter(),
            fileImportsFactory: $this->fileImportsFactory(),
            fqcnResolver: $this->fqcnResolver(),
            tools: $this->generatorTools(),
        );
    }

    public function enumClassGenerator(): EnumClassGenerator
    {
        return new EnumClassGenerator(
            printer: new NettePrinter(),
            fqcnResolver: $this->fqcnResolver(),
        );
    }

    public function valueClassGenerator(): ValueClassGenerator
    {
        return new ValueClassGenerator(
            printer: new NettePrinter(),
            fileImportsFactory: $this->fileImportsFactory(),
            fqcnResolver: $this->fqcnResolver(),
            tools: $this->generatorTools(),
        );
    }

    /**
     * @param list<FileGenerator> $generators
     */
    public function assemble(
        ?PathCollector $pathCollector = null,
        ?PathTransformer $pathTransformer = null,
        ?FileLoader $fileLoader = null,
        ?SchemaDecoder $decoder = null,
        ?FileDumper $fileDumper = null,
        ?array $generators = null,
    ): FilesGenerator
    {
        $generators = $generators ?? [
            $this->classGenerator(),
            $this->enumClassGenerator(),
            $this->valueClassGenerator(),
        ];

        return new FilesGenerator(
            pathCollector: $pathCollector ?? $this->pathCollector(),
            fileLoader: $fileLoader ?? new GetContents(),
            pathTransformer: $pathTransformer ?? $this->pathTransformer(),
            fileDumper: $fileDumper ?? new PutContents(),
            schemaParser: new SchemaParser(),
            schemaFileDecoder: $decoder ?? $this->decoder(),
            typeMappings: $this->typeMappings(),
            generators: $generators,
        );
    }
}
