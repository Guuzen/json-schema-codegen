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
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\DefaultAnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\DefaultCommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultDefaultGenerator;
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

final class NetteFilesGeneratorFactory
{
    /**
     * @param non-empty-string $schemaSuffix
     * @param array<string, class-string> $typeMappings
     */
    public static function create(
        string $baseNamespace,
        string $schemaPath,
        string $outputPath,
        string $schemaSuffix,
        string $undefinedPath,
        array $typeMappings = [],
    ): FilesGenerator
    {
        $suffixExtension = pathinfo($schemaSuffix, PATHINFO_EXTENSION);

        $decoder = match ($suffixExtension) {
            'json' => new JsonDecoder(),
            'yaml' => new YamlDecoder(),
            default => throw new \InvalidArgumentException(
                'By default only json and yaml files are supported.'
            )
        };

        $dirSchemaPath = new AbsoluteUnixDirectoryPath($schemaPath);

        return self::assemble(
            fqcnResolver: new FqcnResolver(
                $dirSchemaPath->toUri(),
                $baseNamespace,
                $schemaSuffix,
            ),
            typeMappings: TypeMappings::create($dirSchemaPath, $typeMappings),
            undefinedUri: $dirSchemaPath->resolve(new RelativeUnixPath($undefinedPath))->toUri(),
            pathCollector: PathsWithSuffix::create($dirSchemaPath, $schemaSuffix),
            pathTransformer: new OutputPathTransformer(
                $dirSchemaPath,
                new AbsoluteUnixDirectoryPath($outputPath),
                $schemaSuffix,
            ),
            decoder: $decoder,
        );
    }

    /**
     * @param list<FileGenerator> $generators
     */
    public static function assemble(
        FqcnResolver $fqcnResolver,
        TypeMappings $typeMappings,
        AbsoluteUri $undefinedUri,
        PathCollector $pathCollector,
        PathTransformer $pathTransformer,
        ?FileLoader $fileLoader = null,
        ?SchemaDecoder $decoder = null,
        ?FileDumper $fileDumper = null,
        ?array $generators = null,
    ): FilesGenerator
    {
        $printer = new NettePrinter();
        $fileImportsFactory = new FileImportsFactory($fqcnResolver, $typeMappings);

        $generatorTools = new GeneratorTools(
            annotationGenerator: new DefaultAnnotationGenerator($fqcnResolver),
            commentGenerator: new DefaultCommentGenerator(new NetteCommentFactory()),
            defaultGenerator: new DefaultDefaultGenerator($fqcnResolver, $undefinedUri, new NetteDefaultValueFactory()),
        );

        return new FilesGenerator(
            pathCollector: $pathCollector,
            fileLoader: $fileLoader ?? new GetContents(),
            pathTransformer: $pathTransformer,
            fileDumper: $fileDumper ?? new PutContents(),
            schemaParser: new SchemaParser(),
            schemaFileDecoder: $decoder ?? new JsonDecoder(),
            typeMappings: $typeMappings,
            generators: $generators ?? [
                new ClassGenerator(
                    printer: $printer,
                    fileImportsFactory: $fileImportsFactory,
                    fqcnResolver: $fqcnResolver,
                    tools: $generatorTools,
                ),
                new ValueClassGenerator(
                    printer: $printer,
                    fileImportsFactory: $fileImportsFactory,
                    fqcnResolver: $fqcnResolver,
                    tools: $generatorTools,
                ),
            ],
        );
    }
}
