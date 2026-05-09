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
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\TypeGenerator\TypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaDecoder;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;
use Guuzen\JsonSchemaCodegen\Schema\JsonDecoder;
use Guuzen\JsonSchemaCodegen\Schema\SchemaParser;

final class NetteFilesGeneratorFactory
{
    /**
     * @param list<FileGenerator> $generators
     */
    public static function create(
        Config $config,
        ?FileLoader $fileLoader = null,
        ?SchemaDecoder $decoder = null,
        ?FileDumper $fileDumper = null,
        ?PathCollector $pathCollector = null,
        ?PathTransformer $pathTransformer = null,
        ?array $generators = null,
    ): FilesGenerator
    {
        $schemaRegistry = new SchemaRegistry();

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
            registry: $schemaRegistry,
            generators: $generators ?? [
                new ClassGenerator(
                    printer: new NettePrinter(),
                    createPhpFile: new CreatePhpFile(self::createFqcnResolver($config)),
                    constructorParameterOrder: new ConstructorParameterOrder(),
                    modifiers: [
                        new CommentModifier(new DefaultCommentGenerator()),
                        new AnnotationModifier(
                            generator: new DefaultAnnotationGenerator(),
                            typeGenerator: new TypeGenerator(self::createFqcnResolver($config), $schemaRegistry),
                        ),
                        new SymfonyValidationModifier(
                            typeGenerator: new TypeGenerator(self::createFqcnResolver($config), $schemaRegistry),
                            generators: SymfonyValidationModifier::defaultGenerators(),
                        ),
                        new OptionalModifier(
                            new DefaultDefaultGenerator(self::createFqcnResolver($config))
                        ),
                    ],
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
