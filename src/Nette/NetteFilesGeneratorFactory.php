<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Config;
use Guuzen\JsonSchemaCodegen\Filesystem\GetContents;
use Guuzen\JsonSchemaCodegen\Filesystem\OutputPathTransformer;
use Guuzen\JsonSchemaCodegen\Filesystem\PathsWithSuffix;
use Guuzen\JsonSchemaCodegen\Filesystem\PutContents;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\ConstructorParameterOrder;
use Guuzen\JsonSchemaCodegen\Generator\FileDumper;
use Guuzen\JsonSchemaCodegen\Generator\FileGenerator;
use Guuzen\JsonSchemaCodegen\Generator\FileLoader;
use Guuzen\JsonSchemaCodegen\Generator\FilesGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PathCollector;
use Guuzen\JsonSchemaCodegen\Generator\PathTransformer;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\DefaultAnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Comment\DefaultCommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultDefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaDecoder;
use Guuzen\JsonSchemaCodegen\Schema\JsonDecoder;
use Guuzen\JsonSchemaCodegen\Schema\SchemaParser;
use Guuzen\JsonSchemaCodegen\Uri\AbsoluteUri;

final class NetteFilesGeneratorFactory
{
    /**
     * @param list<FileGenerator> $generators
     */
    public static function create(
        Config $config,
        AbsoluteUri $undefinedUri,
        ?FileLoader $fileLoader = null,
        ?SchemaDecoder $decoder = null,
        ?FileDumper $fileDumper = null,
        ?PathCollector $pathCollector = null,
        ?PathTransformer $pathTransformer = null,
        ?array $generators = null,
    ): FilesGenerator
    {
        $fqcnResolver = self::createFqcnResolver($config);
        $printer = new NettePrinter();
        $createPhpFile = new CreatePhpFile($fqcnResolver);
        $promotedParameter = new PromotedParameter([
            new CommentModifier(new DefaultCommentGenerator()),
            new AnnotationModifier(
                new DefaultAnnotationGenerator($fqcnResolver),
            ),
            new OptionalModifier(
                generator: new DefaultDefaultGenerator($fqcnResolver),
                fqcnResolver: $fqcnResolver,
                undefinedUri: $undefinedUri,
            ),
        ]);

        return new FilesGenerator(
            pathCollector: $pathCollector ?? PathsWithSuffix::create($config->schemaPath, $config->schemaSuffix),
            fileLoader: $fileLoader ?? new GetContents(),
            pathTransformer: $pathTransformer ?? new OutputPathTransformer(
                $config->schemaPath,
                $config->outputPath,
                $config->schemaSuffix
            ),
            fileDumper: $fileDumper ?? new PutContents(),
            schemaParser: new SchemaParser(),
            schemaFileDecoder: $decoder ?? new JsonDecoder(),
            generators: $generators ?? [
                new ClassGenerator(
                    printer: $printer,
                    createPhpFile: $createPhpFile,
                    constructorParameterOrder: new ConstructorParameterOrder(),
                    promotedParameter: $promotedParameter,
                ),
                new ValueClassGenerator(
                    printer: $printer,
                    createPhpFile: $createPhpFile,
                    promotedParameter: $promotedParameter,
                ),
            ],
        );
    }

    private static function createFqcnResolver(Config $config): FqcnResolver
    {
        return new FqcnResolver(
            $config->schemaPath->toUri(),
            $config->baseNamespace,
            $config->schemaSuffix,
        );
    }
}
