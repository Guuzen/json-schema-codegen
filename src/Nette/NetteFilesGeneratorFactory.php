<?php

declare(strict_types=1);

namespace Guuzen\JsonSchemaCodegen\Nette;

use Guuzen\JsonSchemaCodegen\Filesystem\PutContents;
use Guuzen\JsonSchemaCodegen\Filesystem\GetContents;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\PhpTypeGenerator;
use Guuzen\JsonSchemaCodegen\Schema\JsonDecoder;
use Guuzen\JsonSchemaCodegen\Schema\YamlDecoder;
use Guuzen\JsonSchemaCodegen\Filesystem\OutputPathTransformer;
use Guuzen\JsonSchemaCodegen\Filesystem\PathsWithSuffix;
use Guuzen\JsonSchemaCodegen\Schema\SchemaParser;
use Guuzen\JsonSchemaCodegen\Config;
use Guuzen\JsonSchemaCodegen\Fqcn\FqcnResolver;
use Guuzen\JsonSchemaCodegen\Generator\ConstructorParameterOrder;
use Guuzen\JsonSchemaCodegen\Generator\FilesGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Annotation\DefaultAnnotationGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\DefaultCommentGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\Default\DefaultDefaultGenerator;
use Guuzen\JsonSchemaCodegen\Generator\PropertyGenerator\DefaultTypeGenerator;
use Guuzen\JsonSchemaCodegen\Generator\SchemaDecoder;
use Guuzen\JsonSchemaCodegen\Generator\SchemaRegistry;

final class NetteFilesGeneratorFactory
{
    public static function withJsonDecoder(Config $config): FilesGenerator
    {
        return self::create($config, new JsonDecoder());
    }

    public static function withYamlDecoder(Config $config): FilesGenerator
    {
        return self::create($config, new YamlDecoder());
    }

    private static function create(Config $config, SchemaDecoder $decoder): FilesGenerator
    {
        $fqcnResolver = new FqcnResolver(
            $config->schemaPath->toUri(),
            $config->baseNamespace,
            $config->schemaSuffix,
        );
        $schemaRegistry = new SchemaRegistry();

        $phpTypeGenerator = new PhpTypeGenerator($fqcnResolver, $schemaRegistry);

        return new FilesGenerator(
            pathCollector: PathsWithSuffix::create($config->schemaPath, $config->schemaSuffix),
            fileLoader: new GetContents(),
            pathTransformer: new OutputPathTransformer($config->schemaPath, $config->outputPath, $config->schemaSuffix),
            fileDumper: new PutContents(),
            schemaParser: new SchemaParser(),
            schemaFileDecoder: $decoder,
            registry: $schemaRegistry,
            generators: [
                new ClassGenerator(
                    createPhpFile: new CreatePhpFile($fqcnResolver),
                    constructorParameterOrder: new ConstructorParameterOrder(),
                    modifiers: [
                        new CommentModifier(new DefaultCommentGenerator()),
                        new AnnotationModifier(
                            generator: new DefaultAnnotationGenerator(),
                            phpTypeGenerator: $phpTypeGenerator,
                        ),
                        new SymfonyValidationModifier(
                            typeGenerator: new DefaultTypeGenerator(),
                            phpTypeGenerator: $phpTypeGenerator,
                        ),
                        new OptionalModifier(new DefaultDefaultGenerator($fqcnResolver)),
                    ],
                ),
            ],
        );
    }
}
